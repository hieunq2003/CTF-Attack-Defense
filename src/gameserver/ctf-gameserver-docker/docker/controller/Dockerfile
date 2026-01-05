FROM python:3.13-slim
WORKDIR /app

COPY pyproject.toml .
COPY src ./src
# --- ADD these two lines so pip install . can find scripts/ and README.md ---
COPY scripts ./scripts
COPY README.md ./README.md

RUN pip install --no-cache-dir --upgrade pip setuptools wheel
RUN pip install --no-cache-dir psycopg2-binary ConfigArgParse prometheus_client requests

RUN pip install .

CMD ["python3", "-m", "ctf_gameserver.controller.controller"]
