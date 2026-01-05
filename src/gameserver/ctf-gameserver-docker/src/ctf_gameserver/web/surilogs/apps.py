from django.apps import AppConfig


class SurilogsConfig(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'ctf_gameserver.web.surilogs'
    # Tên hiển thị của app trong Django admin
    verbose_name = 'System log'
