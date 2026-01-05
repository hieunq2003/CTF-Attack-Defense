"""
Django and project specific settings for usage in production.
This version is fully compatible with Docker and hardcodes STATIC_ROOT
to avoid ImproperlyConfigured errors.
"""

import os
from pathlib import Path

# ---------------------------------------------------------------------
# Base directory (container path)
# ---------------------------------------------------------------------
BASE_DIR = Path(__file__).resolve().parent.parent.parent

# ---------------------------------------------------------------------
# Static files (CSS, JavaScript, Images)
# ---------------------------------------------------------------------
STATIC_URL = '/static/'
STATIC_ROOT = '/app/staticfiles'

# Tạo thư mục nếu chưa có
os.makedirs(STATIC_ROOT, exist_ok=True)

# ---------------------------------------------------------------------
# Import base settings
# ---------------------------------------------------------------------
from ctf_gameserver.web.base_settings import *

# ---------------------------------------------------------------------
# HTTPS and Security
# ---------------------------------------------------------------------
HTTPS = False  # Đặt True nếu bạn dùng HTTPS reverse proxy (VD: Nginx SSL)

# ---------------------------------------------------------------------
# Content Security Policy (CSP)
# ---------------------------------------------------------------------
CSP_POLICIES = {
    'base-uri': ["'self'"],
    'connect-src': ["'self'"],
    'form-action': ["'self'"],
    'object-src': ["'none'"],
    'script-src': ["'self'"],
    'style-src': ["'self'"]
}

# ---------------------------------------------------------------------
# Database Configuration
# ---------------------------------------------------------------------
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql',
        'HOST': os.environ.get('DB_HOST', 'db'),
        'PORT': os.environ.get('DB_PORT', '5432'),
        'NAME': os.environ.get('DB_NAME', 'ctf'),
        'USER': os.environ.get('DB_USER', 'ctf'),
        'PASSWORD': os.environ.get('DB_PASSWORD', 'ctfpass'),
        'CONN_MAX_AGE': 60,
    }
}

# ---------------------------------------------------------------------
# Cache Configuration (use local memory instead of memcached)
# ---------------------------------------------------------------------
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.locmem.LocMemCache',
        'LOCATION': 'unique-snowflake',
    }
}

# ---------------------------------------------------------------------
# Email Configuration (optional)
# ---------------------------------------------------------------------
EMAIL_HOST = ''
EMAIL_PORT = 25
EMAIL_HOST_USER = ''
EMAIL_HOST_PASSWORD = ''
EMAIL_USE_TLS = False
EMAIL_USE_SSL = False
DEFAULT_FROM_EMAIL = ''

# ---------------------------------------------------------------------
# Media and Download paths
# ---------------------------------------------------------------------
MEDIA_ROOT = '/app/media'
TEAM_DOWNLOADS_ROOT = '/app/team_downloads'
os.makedirs(MEDIA_ROOT, exist_ok=True)
os.makedirs(TEAM_DOWNLOADS_ROOT, exist_ok=True)

# ---------------------------------------------------------------------
# Sessions
# ---------------------------------------------------------------------
SESSION_ENGINE = 'django.contrib.sessions.backends.cached_db'

# ---------------------------------------------------------------------
# Django secret key (keep private)
# ---------------------------------------------------------------------
SECRET_KEY = 'QJetlFVG6fjncG56ZPFGyd6vZwlpe8r5ezu_iCWiFLA1li-U-8JrbUxLw-8msDjn'

# ---------------------------------------------------------------------
# Hosts, Timezone, Localization
# ---------------------------------------------------------------------
ALLOWED_HOSTS = ['*']
TIME_ZONE = 'Asia/Ho_Chi_Minh'
FIRST_DAY_OF_WEEK = 1
USE_TZ = True

# ---------------------------------------------------------------------
# Logging
# ---------------------------------------------------------------------
LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'handlers': {
        'syslog': {
            'class': 'logging.handlers.SysLogHandler',
            'address': '/dev/log',
        },
        'console': {
            'class': 'logging.StreamHandler',
        },
    },
    'root': {
        'handlers': ['console'],
        'level': 'WARNING',
    },
    'loggers': {
        'django': {
            'handlers': ['syslog', 'console'],
            'level': 'WARNING',
        },
    },
}

# ---------------------------------------------------------------------
# Security cookies (for HTTPS)
# ---------------------------------------------------------------------
if HTTPS:
    CSRF_COOKIE_SECURE = True
    SESSION_COOKIE_SECURE = True

# ---------------------------------------------------------------------
# Debug
# ---------------------------------------------------------------------
DEBUG = False
