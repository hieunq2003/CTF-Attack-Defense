FROM python:3.13-slim

WORKDIR /app

COPY pyproject.toml .
RUN pip install --no-cache-dir --upgrade pip setuptools wheel
RUN pip install --no-cache-dir \
    ConfigArgParse \
    prometheus_client \
    requests \
    psycopg2-binary

COPY src ./src
# --- ADD these two lines so pip install . can find scripts/ and README.md ---
COPY scripts ./scripts
COPY README.md ./README.md

RUN pip install .
ENV PYTHONPATH=/app/src

CMD ["python3", "-m", "ctf_gameserver.submission.submission"]
