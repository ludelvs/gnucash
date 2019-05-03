FROM nginx:1.15.0

RUN apt-get update \
  && apt-get install -y vim curl \
  && apt-get clean \
  && rm -Rf /var/lib/apt/lists/*

ENV LANG C.UTF-8
WORKDIR /var/www/html

COPY .vimrc /root/.vimrc
COPY .htpasswd /etc/nginx/.htpasswd
COPY nginx.conf /etc/nginx/nginx.conf
COPY conf.d/default.conf /etc/nginx/conf.d/

COPY docker-entrypoint.sh /bin/docker-entrypoint.sh
CMD ["/bin/docker-entrypoint.sh"]
