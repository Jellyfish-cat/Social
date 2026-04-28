from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import mysql.connector
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import uvicorn
import warnings
import numpy as np
from datetime import datetime, timedelta
import random
import time

warnings.filterwarnings('ignore')

app = FastAPI(
    title="Social Network Recommender AI",
    description="Hệ thống AI Recommender với công thức Ranking chuẩn (Modularized)"
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

# --- GLOBAL CACHE SYSTEM ---
GLOBAL_CACHE = {
    "df_posts": None,
    "tfidf_matrix": None,
    "tfidf_model": None,
    "last_updated": 0
}
CACHE_TTL = 300 # Cập nhật dữ liệu bài viết mới sau mỗi 5 phút

def get_cached_data():
    global GLOBAL_CACHE
    now = time.time()
    
    # Nếu chưa có cache hoặc cache quá hạn, thực hiện tính toán sẵn (Pre-calculation)
    if GLOBAL_CACHE["df_posts"] is None or (now - GLOBAL_CACHE["last_updated"]) > CACHE_TTL:
        try:
            conn = get_db_connection()
            query_posts = """
                SELECT p.id, p.user_id, p.content, p.created_at,
                       COALESCE(GROUP_CONCAT(DISTINCT t.name SEPARATOR ' '), '') as topics,
                       (SELECT COUNT(*) FROM like_posts WHERE post_id = p.id) as like_count,
                       (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                FROM posts p
                LEFT JOIN post_topic pt ON p.id = pt.post_id
                LEFT JOIN topics t ON pt.topic_id = t.id
                WHERE p.status = 'show'
                GROUP BY p.id
            """
            df_posts = pd.read_sql(query_posts, conn)
            conn.close()

            if not df_posts.empty:
                df_posts['text_features'] = (df_posts['content'].fillna('') + " " + df_posts['topics']).str.lower()
                tfidf = TfidfVectorizer(ngram_range=(1, 2))
                tfidf_matrix = tfidf.fit_transform(df_posts['text_features'])
                
                GLOBAL_CACHE["df_posts"] = df_posts
                GLOBAL_CACHE["tfidf_matrix"] = tfidf_matrix
                GLOBAL_CACHE["tfidf_model"] = tfidf
                GLOBAL_CACHE["last_updated"] = now
                print(f"[AI CACHE] Pre-calculated {len(df_posts)} posts.")
        except Exception as e:
            print(f"[AI CACHE ERROR] {str(e)}")
            
    return GLOBAL_CACHE["df_posts"], GLOBAL_CACHE["tfidf_matrix"], GLOBAL_CACHE["tfidf_model"]

@app.get("/api/recommendations", response_model=RecommendResponse)
def get_recommendations(user_id: int):
    try:
        now = datetime.now()

        # 1. LOAD CACHE
        df_posts, tfidf_matrix, _ = get_cached_data()
        if df_posts is None or df_posts.empty:
            return {"status": "success", "user_id": user_id, "recommended_post_ids": [], "algorithm": "none"}

        df_posts = df_posts.copy()

        # 2. LOAD USER DATA
        conn = get_db_connection()

        df_interacted = pd.read_sql(f"""
            SELECT post_id, created_at FROM (
                SELECT post_id, created_at FROM like_posts WHERE user_id = {user_id}
                UNION
                SELECT post_id, created_at FROM comments WHERE user_id = {user_id}
                UNION
                SELECT post_id, created_at FROM favorites WHERE user_id = {user_id}
            ) t ORDER BY created_at DESC
        """, conn)

        interacted_ids = df_interacted['post_id'].astype(int).tolist()

        all_topics = pd.read_sql(f"""
            SELECT DISTINCT t.name FROM like_posts lp
            JOIN post_topic pt ON lp.post_id = pt.post_id
            JOIN topics t ON pt.topic_id = t.id
            WHERE lp.user_id = {user_id}
        """, conn)['name'].str.lower().tolist()

        recent_topics = pd.read_sql(f"""
            SELECT DISTINCT t.name FROM like_posts lp
            JOIN post_topic pt ON lp.post_id = pt.post_id
            JOIN topics t ON pt.topic_id = t.id
            WHERE lp.user_id = {user_id}
            AND lp.created_at >= NOW() - INTERVAL 3 DAY
        """, conn)['name'].str.lower().tolist()

        following_ids = pd.read_sql(
            f"SELECT following_id FROM follows WHERE follower_id = {user_id}",
            conn
        )['following_id'].astype(int).tolist()

        top_creators = pd.read_sql("""
            SELECT p.user_id FROM posts p
            JOIN like_posts lp ON p.id = lp.post_id
            GROUP BY p.user_id ORDER BY COUNT(*) DESC LIMIT 10
        """, conn)['user_id'].astype(int).tolist()

        reply_post_ids = pd.read_sql(f"""
            SELECT DISTINCT c.post_id FROM comments c
            JOIN comments p ON c.parent_comment_id = p.id
            WHERE p.user_id = {user_id} AND c.user_id != {user_id}
        """, conn)['post_id'].astype(int).tolist()

        conn.close()

        # 3. CONTENT SIMILARITY
        cosine_sim = np.zeros(len(df_posts))

        if not df_interacted.empty:
            idx_map = {pid: i for i, pid in enumerate(df_posts['id'])}
            indices, weights = [], []

            for _, row in df_interacted.iterrows():
                pid = int(row['post_id'])
                if pid in idx_map:
                    indices.append(idx_map[pid])
                    days = (now - pd.to_datetime(row['created_at'])).days
                    weights.append(1 / (days + 1))

            if indices:
                vectors = tfidf_matrix[indices]
                weights = np.array(weights).reshape(-1, 1)
                user_vector = np.asarray(vectors.multiply(weights).sum(axis=0) / weights.sum())
                cosine_sim = cosine_similarity(user_vector, tfidf_matrix).flatten()

        df_posts['content_score'] = cosine_sim

        # 4. RANKING
        scores = []

        for _, row in df_posts.iterrows():
            pid = int(row['id'])

            if pid in interacted_ids:
                scores.append(-999999)
                continue

            # Topic
            topics = row['topics'].lower().split()
            topic_score = 0

            if any(t in topics for t in recent_topics):
                topic_score = 3000
            elif any(t in topics for t in all_topics):
                topic_score = 1000

            # Social
            social_score = 0
            if row['user_id'] in following_ids:
                social_score += 50
            if row['user_id'] in top_creators:
                social_score += 100
            if pid in reply_post_ids:
                social_score += 30

            # Engagement
            engagement_score = row['like_count'] * 2 + row['comment_count'] * 5

            # Freshness
            try:
                hours = (now - pd.to_datetime(row['created_at'])).total_seconds() / 3600
                freshness_score = 200 / (hours + 2)
            except:
                freshness_score = 0

            # Exploration
            exploration_score = random.uniform(0, 20)

            total = (
                row['content_score'] * 250
                + topic_score
                + social_score
                + engagement_score
                + freshness_score
                + exploration_score
            )

            scores.append(total)

        df_posts['score'] = scores

        # 5. SORT + DIVERSITY
        df_sorted = df_posts[df_posts['score'] > -1000].sort_values(by='score', ascending=False)

        result, topic_count = [], {}

        for _, row in df_sorted.iterrows():
            topics = row['topics'].lower().split()

            if any(topic_count.get(t, 0) >= 3 for t in topics):
                continue

            result.append(int(row['id']))

            for t in topics:
                topic_count[t] = topic_count.get(t, 0) + 1

            if len(result) >= 50:
                break

        return {
            "status": "success",
            "user_id": user_id,
            "recommended_post_ids": result,
            "algorithm": "modular_hybrid_v6"
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/user_recommendations", response_model=UserRecommendResponse)
def get_user_recommendations(user_id: int):
    try:
        now = datetime.now()
        conn = get_db_connection()

        # 1. EXCLUDE
        followed = pd.read_sql(
            f"SELECT following_id FROM follows WHERE follower_id = {user_id}",
            conn
        )['following_id'].tolist()

        exclude_ids = set(followed + [user_id])

        df_users = pd.read_sql(
            f"SELECT id FROM users WHERE id NOT IN ({','.join(map(str, exclude_ids)) if exclude_ids else 0}) LIMIT 200",
            conn
        )

        candidate_ids = df_users['id'].tolist()
        if not candidate_ids:
            return {"status": "success", "user_id": user_id, "recommended_user_ids": [], "algorithm": "none"}

        # 2. MUTUAL
        df_mutual = pd.read_sql(f"""
            SELECT f2.following_id as id, COUNT(*) as cnt
            FROM follows f1
            JOIN follows f2 ON f1.following_id = f2.follower_id
            WHERE f1.follower_id = {user_id}
            GROUP BY f2.following_id
        """, conn)

        mutual_map = dict(zip(df_mutual['id'], df_mutual['cnt']))

        # 3. USER TOPICS
        user_topics = " ".join(pd.read_sql(f"""
            SELECT DISTINCT t.name FROM topics t
            JOIN post_topic pt ON t.id = pt.topic_id
            WHERE pt.post_id IN (
                SELECT post_id FROM like_posts WHERE user_id = {user_id}
                UNION
                SELECT post_id FROM comments WHERE user_id = {user_id}
            )
        """, conn)['name'].tolist()).lower()

        # 4. CANDIDATE TOPICS
        df_topics = pd.read_sql(f"""
            SELECT p.user_id, GROUP_CONCAT(t.name SEPARATOR ' ') as topics
            FROM posts p
            JOIN post_topic pt ON p.id = pt.post_id
            JOIN topics t ON pt.topic_id = t.id
            WHERE p.user_id IN ({','.join(map(str, candidate_ids))})
            GROUP BY p.user_id
        """, conn)

        # 5. STATS
        df_stats = pd.read_sql(f"""
            SELECT u.id,
                (SELECT COUNT(*) FROM follows WHERE following_id = u.id) as followers,
                (SELECT MAX(created_at) FROM posts WHERE user_id = u.id) as last_post
            FROM users u WHERE u.id IN ({','.join(map(str, candidate_ids))})
        """, conn)

        stats_map = df_stats.set_index('id').to_dict('index')

        conn.close()

        # 6. RANKING
        tfidf = TfidfVectorizer()
        scores = []

        for uid in candidate_ids:
            # Similarity
            sim_score = 0
            row = df_topics[df_topics['user_id'] == uid]

            if not row.empty and user_topics:
                text = row.iloc[0]['topics'].lower()
                m = tfidf.fit_transform([user_topics, text])
                sim_score = cosine_similarity(m[0:1], m[1:2])[0][0] * 500

            # Mutual
            mutual_score = mutual_map.get(uid, 0) * 150

            # Popularity
            pop = stats_map.get(uid, {}).get('followers', 0) * 5

            # Activity
            act = 0
            last = stats_map.get(uid, {}).get('last_post')
            if last:
                days = (now - pd.to_datetime(last)).days
                act = max(0, 100 - days * 10)

            total = sim_score + mutual_score + pop + act + random.uniform(0, 30)
            scores.append((uid, total))

        scores.sort(key=lambda x: x[1], reverse=True)

        return {
            "status": "success",
            "user_id": user_id,
            "recommended_user_ids": [u for u, _ in scores[:10]],
            "algorithm": "hybrid_user_ranking_v1"
        }

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))