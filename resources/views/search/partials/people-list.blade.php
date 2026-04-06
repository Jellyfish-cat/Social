                    <div class="card shadow-sm border-0 rounded-4 p-3 mb-4">
                        <h5 class="fw-bold mb-3 px-2">Kết quả người dùng</h5>
                        @if(isset($users) && $users->count() > 0)
                            @foreach($users as $user)
                            <div class="d-flex align-items-center justify-content-between p-3 hover-bg-light rounded-4 mb-2 transition-all cursor-pointer">
                                <div class="d-flex align-items-center gap-3">
                                    <a href="{{ route('profile.detail', $user->id) }}">
                                        <img src="{{ asset('storage/' . ($user->profile->avatar ?? 'default-avatar.png')) }}" class="avatar-circle" style="width: 56px; height: 56px;">
                                    </a>
                                    <div>
                                        <a href="{{ route('profile.detail', $user->id) }}" class="fw-bold text-dark text-decoration-none d-block fs-6">{{ $user->profile->display_name ?? $user->name }}</a>
                                        <span class="text-muted small">@ {{ $user->name }}</span> • 
                                         <button class="open-follow follow-count" data-type="follower" data-id="{{$user->id}}">
                                          {{ $user->followers->count() ?? 0 }} người theo dõi</button>
                                    </div>
                                </div>
                                @if($user->id != Auth::id())
                                 @if($user->followers->contains(Auth::id()))
                                    <button class="btn btn-light rounded-pill fw-semibold px-4 btn-sm follow-btn" 
                                    data-id="{{$user->id}}">Đang Theo dõi</button>
                                    @else
                                    <button class="btn btn-primary rounded-pill fw-semibold px-4 btn-sm follow-btn" 
                                    data-id="{{$user->id}}">Theo dõi</button>
                                    @endif
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">    
                                    <i class="bi bi-person-x fs-1 text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Chưa có thông tin hiển thị</h6>
                            </div>
                        @endif
                    </div>
                </div>  

                <!-- Tab Hình Ảnh & Video (PHOTOS/VIDEOS) (Preview Skeleton) -->
                <div class="tab-pane fade" id="photos-content" role="tabpanel">
                    <div class="card shadow-sm border-0 rounded-4 p-3 mb-4">
                        <h5 class="fw-bold mb-3 px-2">Hình ảnh & Video liên quan</h5>
                        <div class="row g-2 mt-2">
                        @if(isset($medias) && $medias->count() > 0)
                            @foreach($medias as $media)
                                <div class="col-4 col-md-3">
                                    <div class="ratio ratio-1x1 rounded-3 overflow-hidden bg-light cursor-pointer position-relative">
                                        @if($media->type == 'image')
                                        <img src="{{ asset('storage/' . $media->file_path) }}" class="object-fit-cover w-100 h-100 transition-all hover-scale">
                                        @else
                                        <video src="{{ asset('storage/' . $media->file_path) }}" class="object-fit-cover w-100 h-100" muted></video>
                                        <div class="position-absolute top-0 end-0 p-1"><i class="bi bi-play-fill text-white fs-5 drop-shadow"></i></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-images fs-1 text-muted mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark">Chưa có thông tin hiển thị</h6>
                            </div>
                        @endif
                        </div>
                    </div>