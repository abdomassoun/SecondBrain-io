# alpine is more secure
FROM nginx:stable-alpine

ENV NGINX_APPLICATION_HOSTNAME application

COPY backend/docker/httpd/vhost.conf /etc/nginx/templates/default.conf.template
COPY backend/public/ /backend-app/backend/public/