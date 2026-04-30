## Video Processing System (Event-Driven, Fault-Tolerant)

A production-oriented video processing system built with PHP and Symfony, designed to handle asynchronous tasks reliably using an event-driven architecture.

The system processes video-related tasks (e.g., transcoding) with support for retries, deduplication, and failure handling. It follows modern backend practices such as the Outbox pattern, observability, and clean architecture.

---

### 🧩 Key Features

* **Asynchronous task processing** with Kafka
* **Retry mechanism** with exponential backoff
* **Deduplication** using Redis to prevent duplicate task execution
* **Outbox pattern** for reliable event delivery
* **Dead Letter Queue (DLQ)** for failed tasks
* **Heartbeat mechanism** for detecting stuck workers
* **Manual retry endpoint** for operational control

---

### 📊 Observability

* Prometheus-compatible **metrics endpoint** (`/metrics`)
* Tracks:

    * processed tasks
    * failed tasks
    * retries
    * processing time
* **Health check endpoint** (`/health`) with dependency checks:

    * PostgreSQL
    * Redis
    * Kafka

---

### 🧪 Testing & Quality

* Unit tests for core business logic
* Integration tests with real PostgreSQL
* Static analysis using PHPStan
* CI pipeline (GitHub Actions):

    * dependency installation
    * database migrations
    * static analysis
    * test execution

---

### 🏗 Architecture

The application follows a layered architecture:

Controller → UseCase → Domain → Infrastructure

* UseCases return DTOs (no entity leakage)
* Clear separation of concerns
* Database access via repositories
* Event-driven communication via Kafka

---

### ⚙️ Tech Stack

* PHP 8.3
* Symfony
* PostgreSQL
* Redis
* Kafka
* Doctrine ORM
* Prometheus (metrics)
* Docker
* GitHub Actions (CI)

---

### 💡 Highlights

* Designed with **fault tolerance** in mind (retry, DLQ, idempotency)
* Implements **event-driven architecture** using Kafka
* Focus on **observability and reliability**
* Demonstrates **production-ready patterns** used in distributed systems

---

### 📌 Future Improvements

* Kubernetes (GKE) deployment
* Managed cloud services (Cloud SQL, Kafka)
* Horizontal scaling of workers
* Advanced alerting and dashboards (Grafana)


### Commands to manage the dev service of the service

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

### App console commands

| Command                    |            Logic of the command            |
|----------------------------|:------------------------------------------:|
| app:jwt:create             |       Create JWT token for the user        |
| app:user:create            |             Create a new user              |
| app:kafka:consume          |             Run Kafka consumer             |
| app:outbox:process         |       Send Outbox messages to Kafka        |
| app:task:retry             |             Run retry of tasks             |
| app:task:heartbeat-monitor | Run heartbeat monitor to retry stale tasks |

### Links

| Link                          |         For what         |
|-------------------------------|:------------------------:|
| http://127.0.0.1:8080/api/doc | Documentation in Swagger |
| http://127.0.0.1:5601         |          Kibana          |
