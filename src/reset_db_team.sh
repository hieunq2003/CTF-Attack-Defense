cd /opt/ctf-gameserver-docker

# Dừng team
docker compose -f docker-compose.teams.yml down

# Xoá volumes để DB init lại từ sis_db.sql (nếu bạn ok mất data cũ)
docker volume rm team1_mysql team2_mysql team3_mysql team4_mysql

# Build lại image teamsvc
docker build -t teamsvc -f docker/challenge/Dockerfile .

# Bật lại 3 team
docker compose -f docker-compose.teams.yml up -d
