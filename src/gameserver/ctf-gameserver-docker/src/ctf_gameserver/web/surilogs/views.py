from django.shortcuts import render
from django.http import StreamingHttpResponse, HttpResponse
import time
import os
from collections import deque

LOG_PATH = "/app/fast.log"


def fastlog_view(request):
    return render(request, "surilogs/fastlog.html")


def fastlog_history(request):
    """
    Trả về đúng 200 dòng cuối của fast.log, giống `tail -n 200 fast.log`
    """
    max_lines = 200
    try:
        with open(LOG_PATH, "r") as f:
            last_lines = deque(f, maxlen=max_lines)
    except FileNotFoundError:
        last_lines = []

    data = "".join(last_lines)
    return HttpResponse(data, content_type="text/plain")


def fastlog_stream(request):
    """
    Giống `tail -f`: nhảy tới EOF và chỉ gửi những dòng mới.
    """
    def event_stream():
        while True:
            try:
                with open(LOG_PATH, "r") as f:
                    # Nhảy tới cuối file → chỉ đọc phần mới
                    f.seek(0, os.SEEK_END)

                    while True:
                        line = f.readline()
                        if not line:
                            time.sleep(0.2)  # thăm dò ~5 lần/giây
                            continue
                        yield f"data: {line.rstrip()}\n\n"
            except FileNotFoundError:
                # Nếu file chưa tồn tại, thông báo nhẹ và thử lại
                yield "data: [fast.log not found]\n\n"
                time.sleep(1)

    resp = StreamingHttpResponse(event_stream(), content_type="text/event-stream")
    resp["Cache-Control"] = "no-cache"
    resp["X-Accel-Buffering"] = "no"
    # Cho Chrome bớt cảnh báo COOP/COEP trên HTTP
    resp["Cross-Origin-Opener-Policy"] = "unsafe-none"
    resp["Cross-Origin-Embedder-Policy"] = "unsafe-none"
    return resp
