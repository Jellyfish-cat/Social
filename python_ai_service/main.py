from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import mysql.connector
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import uvicorn
import numpy as np
from datetime import datetime, timedelta
import random
import time
import requests

app = FastAPI(
    title="Social Network Recommender AI",
    description="Hệ thống AI Recommender Optimized (Security & Performance)"
)

def get_db_connection():
    return mysql.connector.connect(
        host="127.0.0.1", user="root", password="", database="social"
    )

class RecommendResponse(BaseModel):
    status: str
    user_id: int
    recommended_post_ids: list[int]
    algorithm: str

class UserRecommendResponse(BaseModel):
    status: str
    user_id: int
    recommended_user_ids: list[int]
    algorithm: str

class RankSearchRequest(BaseModel):
    user_id: int
    cand_user_ids: list[int]
    cand_post_ids: list[int]

class RankSearchResponse(BaseModel):
    status: str
    user_ids: list[int]
    post_ids: list[int]

def get_data():
    try:
        conn = get_db_connection()
        query_posts = """
            SELECT p.id, p.user_id, p.content, p.created_at, u.role as author_role,
                   COALESCE(GROUP_CONCAT(DISTINCT t.name SEPARATOR ' '), '') as topics,
                   (SELECT COUNT(*) FROM like_posts WHERE post_id = p.id) as like_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_topic pt ON p.id = pt.post_id
            LEFT JOIN topics t ON pt.topic_id = t.id
            WHERE p.status = 'show'
            GROUP BY p.id
        """
        df_posts = pd.read_sql(query_posts, conn)
        df_likes = pd.read_sql("SELECT user_id, post_id FROM like_posts", conn)
        conn.close()

        if df_posts.empty:
            return None, None, None
        #tính tf-idf vector 
        df_posts['text_features'] = (df_posts['content'].fillna('') + " " + df_posts['topics']).str.lower()
        tfidf = TfidfVectorizer(ngram_range=(1, 2))
        tfidf_matrix = tfidf.fit_transform(df_posts['text_features'])
        
        user_item_matrix = None
        if not df_likes.empty:
            user_item_matrix = df_likes.pivot_table(index='user_id', columns='post_id', aggfunc='size', fill_value=0)
            
        return df_posts, tfidf_matrix, user_item_matrix
    except Exception as e:
        print(f"[AI DATA ERROR] {str(e)}")
        return None, None, None

# --- MODULAR RECOMENDER FUNCTIONS ---

def compute_cf_scores(user_id, user_item_matrix, interacted_set):
    scores = {}
    if user_item_matrix is None or user_id not in user_item_matrix.index:
        return scores
    try:
        user_vector = user_item_matrix.loc[[user_id]]
        similarities = cosine_similarity(user_vector, user_item_matrix).flatten()
        similar_idx = similarities.argsort()[-11:-1][::-1]
        similar_user_ids = user_item_matrix.index[similar_idx]

        for uid in similar_user_ids:
            weight = similarities[user_item_matrix.index.get_loc(uid)]
            if weight <= 0: continue
            # Lấy các bài viết mà người giống đã like
            liked_posts = user_item_matrix.columns[user_item_matrix.loc[uid] > 0]
            for pid in liked_posts:
                if pid not in interacted_set: # Bỏ qua những bài đã tương tác rồi
                    scores[pid] = scores.get(pid, 0) + (weight * 500)
    except Exception as e:
        print(f"[CF ERROR] {str(e)}")
    return scores

def compute_content_score(df_posts, tfidf_matrix, df_interacted, now):
    cosine_sim = np.zeros(len(df_posts))
    if df_interacted.empty:
        return cosine_sim

    idx_map = {pid: i for i, pid in enumerate(df_posts['id'])}
    indices, weights = [], []

    for _, row in df_interacted.iterrows():
        pid = int(row['post_id'])
        if pid in idx_map:
            indices.append(idx_map[pid])
            days = (now - pd.to_datetime(row['created_at'])).days
            weights.append(1 / (days + 1))

    if not indices:
        return cosine_sim

    vectors = tfidf_matrix[indices]
    weights = np.array(weights).reshape(-1, 1)
    user_vector = np.asarray(vectors.multiply(weights).sum(axis=0) / weights.sum())
    return cosine_similarity(user_vector, tfidf_matrix).flatten()

def calculate_score(row, context):
    pid = int(row['id'])
    
    # 1. LOGIC BÀI VIẾT CỦA CHÍNH MÌNH 
    if row['user_id'] == context["user_id"]:
        # Tính thời gian từ lúc đăng (phút)
        age_min = (context["now"] - pd.to_datetime(row['created_at'])).total_seconds() / 60
        # Nếu chưa reload và bài mới đăng (< 10 phút) -> Hiện Top
        if not context.get("is_reload", False) and age_min < 10:
            return 10000000
        else:
            return -999999 
    # 2. LỌC BÀI ĐÃ TƯƠNG TÁC (Like, Comment, Favorite)
    if pid in context["interacted_ids"]:
        return -999999

    # Cold start logic
    cold = 0
    if context["is_cold_start"]:
        cold += row['like_count'] * 10

    # Collaborative Filtering score
    collab = context["cf_scores"].get(pid, 0)

    # Topic matching logic
    topics = row['topics'].lower().split()
    topic_val = 0
    if any(t in context["recent_topics"] for t in topics):
        topic_val = 3000
    elif any(t in context["all_topics"] for t in topics):
        topic_val = 1000

    # Social signals logic
    social = 0
    if row['user_id'] in context["following"]:
        social += 50
    # Engagement logic
    engagement = row['like_count'] * 2 + row['comment_count'] * 5

    # Freshness logic
    try:
        hours = (context["now"] - pd.to_datetime(row['created_at'])).total_seconds() / 3600
        freshness = 200 / (hours + 2)
    except:
        freshness = 0

    # Exploration factor
    exploration = random.uniform(0, 20)

    return (
        row['content_score'] * 250
        + collab + cold + topic_val + social
        + engagement + freshness + exploration
    )

def diversify(df_sorted, limit=50):
    result, topic_count = [], {}
    for _, row in df_sorted.iterrows():
        topics = row['topics'].lower().split()
        if any(topic_count.get(t, 0) >= 5 for t in topics):
            continue
        result.append(int(row['id']))
        for t in topics:
            topic_count[t] = topic_count.get(t, 0) + 1
        if len(result) >= limit:
            break
    return result

# --- API ENDPOINTS ---

@app.get("/api/recommendations", response_model=RecommendResponse)
def get_recommendations(user_id: int, is_reload: bool = False, offset: int = 0, limit: int = 50):
    try:
        now = datetime.now()
        df_posts, tfidf_matrix, user_item_matrix = get_data()
        if df_posts is None or df_posts.empty:
            return {"status": "success", "user_id": user_id, "recommended_post_ids": [], "algorithm": "none"}

        df_posts = df_posts.copy()
        conn = get_db_connection()
        
        query_interacted = """
            SELECT post_id, created_at FROM (
                SELECT post_id, created_at FROM like_posts WHERE user_id = %s
                UNION SELECT post_id, created_at FROM comments WHERE user_id = %s
                UNION SELECT post_id, created_at FROM favorites WHERE user_id = %s
            ) t ORDER BY created_at DESC
        """
        df_interacted = pd.read_sql(query_interacted, conn, params=(user_id, user_id, user_id))
        interacted_ids = set(df_interacted['post_id'].astype(int).tolist())
        is_cold_start = not interacted_ids

        all_topics = set(pd.read_sql("SELECT DISTINCT t.name FROM like_posts lp JOIN post_topic pt ON lp.post_id = pt.post_id JOIN topics t ON pt.topic_id = t.id WHERE lp.user_id = %s", conn, params=(user_id,))['name'].str.lower().tolist())
        recent_topics = set(pd.read_sql("SELECT DISTINCT t.name FROM like_posts lp JOIN post_topic pt ON lp.post_id = pt.post_id JOIN topics t ON pt.topic_id = t.id WHERE lp.user_id = %s AND lp.created_at >= NOW() - INTERVAL 3 DAY", conn, params=(user_id,))['name'].str.lower().tolist())
        following_ids = set(pd.read_sql("SELECT following_id FROM follows WHERE follower_id = %s", conn, params=(user_id,))['following_id'].astype(int).tolist())
        top_creators = set(pd.read_sql("SELECT p.user_id FROM posts p JOIN like_posts lp ON p.id = lp.post_id GROUP BY p.user_id ORDER BY COUNT(*) DESC LIMIT 10", conn)['user_id'].astype(int).tolist())
        reply_post_ids = set(pd.read_sql("SELECT DISTINCT c.post_id FROM comments c JOIN comments p ON c.parent_comment_id = p.id WHERE p.user_id = %s AND c.user_id != %s", conn, params=(user_id, user_id))['post_id'].astype(int).tolist())
        conn.close()

        cf_scores = compute_cf_scores(user_id, user_item_matrix, interacted_ids)
        df_posts['content_score'] = compute_content_score(df_posts, tfidf_matrix, df_interacted, now)

        context = {
            "user_id": user_id,
            "interacted_ids": interacted_ids,
            "is_cold_start": is_cold_start,
            "cf_scores": cf_scores,
            "recent_topics": recent_topics,
            "all_topics": all_topics,
            "following": following_ids,
            "top_creators": top_creators,
            "reply_posts": reply_post_ids,
            "now": now,
            "is_reload": is_reload
        }

        df_posts['score'] = df_posts.apply(lambda row: calculate_score(row, context), axis=1)
        df_sorted = df_posts[df_posts['score'] > -1000].sort_values(by='score', ascending=False)
        
        # Yêu cầu hàm lấy thừa ra một chút để phục vụ phân trang
        full_result = diversify(df_sorted, limit=offset + limit)
        # Cắt đúng đoạn theo yêu cầu của Frontend (Infinite Scroll)
        result = full_result[offset : offset + limit]

        return {
            "status": "success",
            "user_id": user_id,
            "recommended_post_ids": result,
            "algorithm": "hybrid_v12_3min_rule"
        }
    except Exception as e:
        print(f"[AI ERROR] {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/rank_search_suggestions", response_model=RankSearchResponse)
def rank_search_suggestions(req: RankSearchRequest):
    try:
        now = datetime.now()
        cand_user_ids = req.cand_user_ids
        cand_post_ids = req.cand_post_ids
        user_id = req.user_id

        if user_id <= 0:
            return {"status": "success", "user_ids": cand_user_ids, "post_ids": cand_post_ids}

        # --- 1. LẤY DỮ LIỆU NGỮ CẢNH (Y hệt Recommender) ---
        conn = get_db_connection()
        query_interacted = """
            SELECT post_id, created_at FROM (
                SELECT post_id, created_at FROM like_posts WHERE user_id = %s
                UNION SELECT post_id, created_at FROM comments WHERE user_id = %s
                UNION SELECT post_id, created_at FROM favorites WHERE user_id = %s
            ) t ORDER BY created_at DESC
        """
        df_interacted = pd.read_sql(query_interacted, conn, params=(user_id, user_id, user_id))
        interacted_ids = set(df_interacted['post_id'].astype(int).tolist())

        all_topics = set(pd.read_sql("SELECT DISTINCT t.name FROM like_posts lp JOIN post_topic pt ON lp.post_id = pt.post_id JOIN topics t ON pt.topic_id = t.id WHERE lp.user_id = %s", conn, params=(user_id,))['name'].str.lower().tolist())
        recent_topics = set(pd.read_sql("SELECT DISTINCT t.name FROM like_posts lp JOIN post_topic pt ON lp.post_id = pt.post_id JOIN topics t ON pt.topic_id = t.id WHERE lp.user_id = %s AND lp.created_at >= NOW() - INTERVAL 3 DAY", conn, params=(user_id,))['name'].str.lower().tolist())
        following_ids = set(pd.read_sql("SELECT following_id FROM follows WHERE follower_id = %s", conn, params=(user_id,))['following_id'].astype(int).tolist())
        top_creators = set(pd.read_sql("SELECT p.user_id FROM posts p JOIN like_posts lp ON p.id = lp.post_id GROUP BY p.user_id ORDER BY COUNT(*) DESC LIMIT 10", conn)['user_id'].astype(int).tolist())
        reply_post_ids = set(pd.read_sql("SELECT DISTINCT c.post_id FROM comments c JOIN comments p ON c.parent_comment_id = p.id WHERE p.user_id = %s AND c.user_id != %s", conn, params=(user_id, user_id))['post_id'].astype(int).tolist())
        
        # Lấy thông tin Mutual Friends cho User Ranking
        df_mutual = pd.read_sql("SELECT f2.following_id as id, COUNT(*) as cnt FROM follows f1 JOIN follows f2 ON f1.following_id = f2.follower_id WHERE f1.follower_id = %s GROUP BY f2.following_id", conn, params=(user_id,))
        mutual_map = dict(zip(df_mutual['id'], df_mutual['cnt']))

        # --- 2. LẤY DỮ LIỆU CHI TIẾT ỨNG VIÊN (Candidates) ---
        df_posts, tfidf_matrix, user_item_matrix = get_data()
        conn.close()

        # --- 3. TÍNH TOÁN ĐIỂM SỐ (Scoring) ---
        # A. Ranking Users
        def score_user(uid):
            score = 0
            if uid in following_ids: score += 2000
            score += mutual_map.get(uid, 0) * 150
            return score + random.uniform(0, 20)
        
        ranked_users = sorted(cand_user_ids, key=score_user, reverse=True)

        # B. Ranking Posts (Sử dụng calculate_score dùng chung)
        if not cand_post_ids or df_posts is None:
            return {"status": "success", "user_ids": ranked_users, "post_ids": cand_post_ids}

        # Tính Content Score dựa trên TF-IDF cho các ứng viên tìm kiếm
        cf_scores = compute_cf_scores(user_id, user_item_matrix, interacted_ids)
        content_scores = compute_content_score(df_posts, tfidf_matrix, df_interacted, now)
        df_posts['content_score'] = content_scores

        context = {
            "user_id": user_id,
            "interacted_ids": interacted_ids,
            "is_cold_start": not interacted_ids,
            "cf_scores": cf_scores,
            "recent_topics": recent_topics,
            "all_topics": all_topics,
            "following": following_ids,
            "top_creators": top_creators,
            "reply_posts": reply_post_ids,
            "now": now,
            "is_search_ranking": True # Flag để nhận biết đang rank search
        }

        # Chỉ lọc những bài nằm trong danh sách ứng viên từ Search
        cand_set = set(map(int, cand_post_ids))
        df_candidates = df_posts[df_posts['id'].isin(cand_set)].copy()
        
        if df_candidates.empty:
            return {"status": "success", "user_ids": ranked_users, "post_ids": []}

        df_candidates['score'] = df_candidates.apply(lambda row: calculate_score(row, context), axis=1)
        ranked_posts = df_candidates.sort_values(by='score', ascending=False)['id'].tolist()

        return {"status": "success", "user_ids": ranked_users, "post_ids": ranked_posts}
    except Exception as e:
        print(f"[AI RANK SEARCH ERROR] {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/rank_search_results", response_model=RankSearchResponse)
def rank_search_results(req: RankSearchRequest):
    # Sử dụng chung logic cao cấp
    return rank_search_suggestions(req)

@app.get("/api/user_recommendations", response_model=UserRecommendResponse)
def get_user_recommendations(user_id: int):
    try:
        now = datetime.now()
        conn = get_db_connection()
        followed = set(pd.read_sql("SELECT following_id FROM follows WHERE follower_id = %s", conn, params=(user_id,))['following_id'].tolist())
        exclude_ids = followed | {user_id}
        df_users = pd.read_sql(f"SELECT id FROM users WHERE id NOT IN ({','.join(map(str, exclude_ids)) if exclude_ids else 0}) AND status = 'show' AND role = 'user' LIMIT 200", conn)
        candidate_ids = df_users['id'].tolist()
        if not candidate_ids: return {"status": "success", "user_id": user_id, "recommended_user_ids": [], "algorithm": "none"}
        df_mutual = pd.read_sql("SELECT f2.following_id as id, COUNT(*) as cnt FROM follows f1 JOIN follows f2 ON f1.following_id = f2.follower_id WHERE f1.follower_id = %s GROUP BY f2.following_id", conn, params=(user_id,))
        mutual_map = dict(zip(df_mutual['id'], df_mutual['cnt']))
        user_topics_df = pd.read_sql("SELECT DISTINCT t.name FROM topics t JOIN post_topic pt ON t.id = pt.topic_id WHERE pt.post_id IN (SELECT post_id FROM like_posts WHERE user_id = %s UNION SELECT post_id FROM comments WHERE user_id = %s)", conn, params=(user_id, user_id))
        user_topics = " ".join(user_topics_df['name'].tolist()).lower()
        df_topics = pd.read_sql(f"SELECT p.user_id, GROUP_CONCAT(t.name SEPARATOR ' ') as topics FROM posts p JOIN post_topic pt ON p.id = pt.post_id JOIN topics t ON pt.topic_id = t.id WHERE p.user_id IN ({','.join(map(str, candidate_ids))}) GROUP BY p.user_id", conn)
        df_stats = pd.read_sql(f"SELECT u.id, (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers, (SELECT MAX(created_at) FROM posts WHERE user_id = u.id) as last_post FROM users u WHERE u.id IN ({','.join(map(str, candidate_ids))})", conn)
        stats_map = df_stats.set_index('id').to_dict('index')
        conn.close()
        tfidf = TfidfVectorizer(); scores = []
        for uid in candidate_ids:
            sim_score = 0; row = df_topics[df_topics['user_id'] == uid]
            if not row.empty and user_topics:
                try:
                    text = row.iloc[0]['topics'].lower()
                    m = tfidf.fit_transform([user_topics, text])
                    sim_score = cosine_similarity(m[0:1], m[1:2])[0][0] * 500
                except: sim_score = 0
            mutual_score = mutual_map.get(uid, 0) * 150
            pop = stats_map.get(uid, {}).get('followers', 0) * 5
            act = 0; last = stats_map.get(uid, {}).get('last_post')
            if last:
                days = (now - pd.to_datetime(last)).days
                act = max(0, 100 - days * 10)
            total = sim_score + mutual_score + pop + act + random.uniform(0, 30)
            scores.append((uid, total))
        scores.sort(key=lambda x: x[1], reverse=True)
        return {"status": "success", "user_id": user_id, "recommended_user_ids": [u for u, _ in scores[:8]], "algorithm": "hybrid_user_ranking_v1_optimized"}
    except Exception as e: raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)