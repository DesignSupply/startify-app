# ベースイメージを指定
FROM mariadb:11.0

# 環境変数を設定
ENV MYSQL_ROOT_PASSWORD=${DB_PASSWORD}
ENV MYSQL_DATABASE=${DB_DATABASE}
ENV MYSQL_USER=${DB_USERNAME}
ENV MYSQL_PASSWORD=${DB_PASSWORD}

# タイムゾーンを日本に設定
RUN apt-get update && apt-get install -y tzdata && \
    ln -sf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
    echo "Asia/Tokyo" > /etc/timezone

# ポートを公開
EXPOSE 3306 
