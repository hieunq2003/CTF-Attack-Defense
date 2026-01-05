# src/ctf_gameserver/web/surilogs/admin.py
from django.contrib import admin
from django.http import HttpResponseRedirect
from django.urls import reverse

from .models import SuriLog


class SuriLogAdmin(admin.ModelAdmin):
    """
    Dùng model này như một “menu item”.
    Khi click vào 'System log' trong Django admin,
    thay vì list object, ta redirect sang trang fastlog.
    """

    # Không cho add/change/delete
    def has_add_permission(self, request):
        return False

    def has_change_permission(self, request, obj=None):
        return False

    def has_delete_permission(self, request, obj=None):
        return False

    # Khi bấm vào 'System log' trên admin index:
    def changelist_view(self, request, extra_context=None):
        # 'fastlog' là name trong urls.py:
        # url(r'^admin/suricata/fastlog/$', ..., name='fastlog')
        return HttpResponseRedirect(reverse('fastlog'))
