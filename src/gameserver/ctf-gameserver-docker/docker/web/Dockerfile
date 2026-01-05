FROM python:3.13-slim

WORKDIR /app

# Copy metadata + source
COPY pyproject.toml .
COPY src ./src
#Them suricata
COPY src/ctf_gameserver/web/templates /usr/local/lib/python3.13/site-packages/ctf_gameserver/web/templates
COPY conf/web/prod_settings.py ./src/ctf_gameserver/web/prod_settings.py
COPY scripts ./scripts
COPY README.md ./README.md

# Install build tools
RUN pip install --no-cache-dir --upgrade pip setuptools wheel

# Install dependencies for pylibmc
RUN apt-get update && apt-get install -y --no-install-recommends \
    gcc libmemcached-dev libsasl2-dev zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Python dependencies
RUN pip install --no-cache-dir \
    Django==4.2.* gunicorn psycopg2-binary argon2-cffi \
    Markdown requests prometheus_client ConfigArgParse pylibmc

# Install project
RUN pip install .

ENV DJANGO_SETTINGS_MODULE=ctf_gameserver.web.prod_settings

# Default command
CMD ["gunicorn", "-b", "0.0.0.0:8000", "ctf_gameserver.web.wsgi:application"]
