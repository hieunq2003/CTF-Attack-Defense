"""
Production settings for CTF Gameserver (clean and fixed version).
This file is optimized for Docker deployment with static/media files
stored in /app/staticfiles and /app/media, which are shared with Nginx.
"""

# pylint: disable=wildcard-import, unused-wildcard-import
from ctf_gameserver.web.base_settings import *
import os

# ---------------------------------------------------------------------
# Base directory (force absolute path inside container)
# ---------------------------------------------------------------------
# Django code is under /app/src, but we want static & media under /app/
BASE_DIR = "/app"

# ---------------------------------------------------------------------
# HTTPS and security
# ---------------------------------------------------------------------
HTTPS = False  # Set to True if serving over HTTPS (with TLS termination)

# ---------------------------------------------------------------------
# Content Security Policy
# ---------------------------------------------------------------------
CSP_POLICIES = {
    'base-uri': ["'self'"],
    'connect-src': ["'self'"],
    'form-action': ["'self'"],
    'object-src': ["'none'"],
    'script-src': ["'self'", "'unsafe-inline'"],
    'style-src': ["'self'", "'unsafe-inline'"],
}

# ---------------------------------------------------------------------
# Database
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
# Cache (local memory for simplicity)
# ---------------------------------------------------------------------
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.locmem.LocMemCache',
        'LOCATION': 'unique-snowflake',
    }
}

# ---------------------------------------------------------------------
# Email configuration
# ---------------------------------------------------------------------
EMAIL_HOST = 'smtp.gmail.com'
EMAIL_PORT = 587
EMAIL_HOST_USER = 'n21dcat021@student.ptithcm.edu.vn'
EMAIL_HOST_PASSWORD = 'fmyoplzreglgeoxe'
EMAIL_USE_TLS = True
EMAIL_USE_SSL = False
DEFAULT_FROM_EMAIL = 'PTITHCM A&D CTF'

# ---------------------------------------------------------------------
# Static and media files
# ---------------------------------------------------------------------
STATIC_URL = '/static/'
STATIC_ROOT = os.path.join(BASE_DIR, 'staticfiles')

MEDIA_URL = '/media/'
MEDIA_ROOT = os.path.join(BASE_DIR, 'media')

# Optional: per-team downloads (not served by web server)
TEAM_DOWNLOADS_ROOT = os.path.join(BASE_DIR, 'team_downloads')

# ---------------------------------------------------------------------
# Sessions
# ---------------------------------------------------------------------
SESSION_ENGINE = 'django.contrib.sessions.backends.cached_db'

# ---------------------------------------------------------------------
# Secret key
# ---------------------------------------------------------------------
SECRET_KEY = 'QJetlFVG6fjncG56ZPFGyd6vZwlpe8r5ezu_iCWiFLA1li-U-8JrbUxLw-8msDjn'

# ---------------------------------------------------------------------
# Host & Timezone
# ---------------------------------------------------------------------
ALLOWED_HOSTS = ['*']
TIME_ZONE = 'Asia/Ho_Chi_Minh'
FIRST_DAY_OF_WEEK = 1

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
    },
    'loggers': {
        'django': {
            'handlers': ['syslog'],
            'level': 'WARNING',
        },
    },
}

# ---------------------------------------------------------------------
# Security cookies
# ---------------------------------------------------------------------
if HTTPS:
    CSRF_COOKIE_SECURE = True
    SESSION_COOKIE_SECURE = True

# ---------------------------------------------------------------------
# Debug
# ---------------------------------------------------------------------
DEBUG = True
