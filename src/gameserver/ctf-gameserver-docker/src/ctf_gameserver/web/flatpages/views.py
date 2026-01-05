from django.shortcuts import render, get_object_or_404
from django.utils import timezone

from .models import Flatpage
from ..scoring.models import GameControl


def flatpage(request, category=None, slug=''):
    # --- Lấy trang hiện tại ---
    if category is None:
        page = get_object_or_404(Flatpage, category=None, slug=slug)
    else:
        page = get_object_or_404(Flatpage, category__slug=category, slug=slug)

    # --- Sidebar ---
    if page.category is not None and page.has_siblings():
        sidebar_links = page.siblings
    else:
        sidebar_links = []

    # --- GameControl ---
    game = GameControl.objects.first()

    competition_name = game.competition_name if game else ""
    game_start = game.start if game else None
    game_end = game.end if game else None

    # --- Logic trạng thái ---
    now = timezone.now()
    if game_start and now < game_start:
        game_state = "upcoming"
    elif game_end and now > game_end:
        game_state = "ended"
    else:
        game_state = "running"

    return render(request, 'flatpage.html', {
        'page': page,
        'sidebar_links': sidebar_links,

        # Data gửi ra template
        'competition_name': competition_name,
        'game_start': game_start,
        'game_end': game_end,
        'game_state': game_state,
    })
