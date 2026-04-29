# Video task processor service

## Commands to manage the dev service of the service

| Command     | Description                                                                                                                   |
|-------------|-------------------------------------------------------------------------------------------------------------------------------|
| up          | Starts all services defined in the default docker-compose configuration.                                                      |
| up-elk      | Starts services using both the default and ELK docker-compose configurations.                                                 |
| build       | Builds images (if needed) and starts all services.                                                                            |
| down        | Stops and removes all running containers, networks, and related resources.                                                    |
| bash        | Opens an interactive bash shell inside the PHP container.                                                                     |
| migrate     | Runs database migrations using Doctrine.                                                                                      |
| cache-clear | Clears the Symfony application cache.                                                                                         |
| init        | Initializes the project: builds containers, installs dependencies, creates the database (if not exists), and runs migrations. |

## App console commands

| Command            |     Logic of the command      |
|--------------------|:-----------------------------:|
| app:jwt:create     | Create JWT token for the user |
| app:user:create    |       Create a new user       |
| app:kafka:consume  |      Run Kafka consumer       |
| app:outbox:process | Send Outbox messages to Kafka |
| app:task:retry     |      Run retry of tasks       |

## Links

| Link                          |         For what         |
|-------------------------------|:------------------------:|
| http://127.0.0.1:8080/api/doc | Documentation in Swagger |
| http://127.0.0.1:5601         |          Kibana          |
