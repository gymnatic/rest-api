# Symfony Docker REST API

A [Symfony](https://symfony.com) based REST API with a connection to the external API https://gorest.co.in/.


## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Keep data in sync with API

* use caching system to prevent too many calls to the api
    * check for example header status: if status *304 (Not-Modified)*, the cached response version can be used
    * you can also use the ETag from the header to check if something has changed
* the data can also be updated via cron jobs
    * synchronizing script will be executed once each time frame (e.g. 1 hour)
    * all other requests will use the database items
* you can also check with a checksum (e.g. md5), if the data was modified
    * checksum will be stored in the database
    * `SELECT` calls are more efficient than `UPDATE` calls

## Deploy for production

* each api version has its own version directory
* to minimize high amout of user request
    * we can seperate it in multiple chuncks
* add pagination in api calls (getting not all data at once)
* we can also use a job queue that will handle all request
* when we have big data in place, we should use PostgreSQL:
    * execute more complex requests
    * better performance with high traffic (read and write processes)
* security:
    * use API keys for user request
    * make usage of Unit tests, application tests, etc. (QA)
    * make usage of backup and restore system
* use code versioning and deployment pipelines (CI/CD)
* scalability: cloud infrastructure like AWS, GoogleCloud etc. auto scaling and load balancing
* monitoring and logging: log errors in real time
* write documentations for understanding code implementations