FROM docker.elastic.co/logstash/logstash:7.6.2

USER root

# Install Curator
RUN yum -y install https://packages.elastic.co/curator/5/centos/7/Packages/elasticsearch-curator-5.8.1-1.x86_64.rpm

# Install AWS CLI and JQ required for loading variables
RUN yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
RUN yum -y install jq unzip;

RUN curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"; \
    unzip awscliv2.zip; \
    ./aws/install; \
    aws --version;

COPY ./docker/logstash/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Install MYSQL connector
RUN yum -y install mysql-connector-java

# Install JDBC plugin
RUN bin/logstash-plugin install logstash-integration-jdbc
RUN bin/logstash-plugin install logstash-output-elasticsearch

# Clear existing config
RUN rm -rf /usr/share/logstash/config
RUN rm -rf /usr/share/logstash/pipeline

# Copy custom config
COPY ./docker/logstash/config /usr/share/logstash/config
COPY ./docker/logstash/pipeline /usr/share/logstash/pipeline
COPY ./docker/logstash/curator /usr/share/curator

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
