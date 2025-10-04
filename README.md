# Mini Task Manager 

This is the **backend** part of the Mini Task Manager project, built with **Core PHP (no frameworks)**.  
The entry point is a single file: **`api/public/tasks.php`**, which exposes a REST API to manage tasks.  

The codebase is organized into folders (Router, Controller, Service, Repository) for clarity, but **everything runs on native PHP** without frameworks.

---

##  Contents
- [Requirements](#requirements)
- [Project Structure](#project-structure)
- [Setup](#setup)
- [Database Configuration](#database-configuration)
- [Running the API](#running-the-api)
- [API Endpoints](#api-endpoints)
- [Implementation Details](#implementation-details)
- [Common Issues & Fixes](#common-issues--fixes)
- [Assumptions](#assumptions)

---

##  Requirements
- PHP **8.1+** with PDO enabled  
- Composer  
- MySQL or PostgreSQL installed locally  

---

##  Project Structure


api/
├─ public/
│ └─ tasks.php # Single entry point (REST API)
├─ src/
│ ├─ Application/ # Services (business logic)
│ ├─ Domain/ # Entities
│ ├─ Infrastructure/ # DB connection, repositories
│ ├─ Interface/Http/ # Router, Controllers
│ └─ routes/api.php # Route definitions
├─ composer.json
├─ .env.example # Example environment config
└─ schema.sql # DB schema + seed data


---

##  Setup

### 1️ Clone the repository
```bash
git clone https://github.com/username/mini-task-manager.git
cd mini-task-manager/api

Install dependencies
composer install


Copy the environment file
cp .env.example .env


Configure .env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=task_manager
DB_USER=root
DB_PASS=secret


Database Configuration
MySQL
CREATE DATABASE task_manager;
USE task_manager;
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    status ENUM('pending','done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


Running the API
Start PHP’s built-in server from the /api folder:
http://127.0.0.1:8080/api/tasks

Or manually:
php -S 127.0.0.1:8080 -t public public/tasks.php


API Endpoints
Get all tasks
GET /tasks


Add a new task
POST /tasks

Update a task
PUT /tasks/{id}

Delete a task
DELETE /tasks/{id}



Implementation Details

Router: Custom PHP router that matches methods and paths.
Controller: Handles request/response logic.
Service: Business logic, input normalization, validation.
Repository: PDO with prepared statements for safe SQL queries.
HTTP Helper: Centralized JSON response handler (ok, created, error, etc.).
CORS: Configured in Http::setupCors() for frontend communication.



Common Issues & Fixes

Issue	Cause / Solution
404 Not Found	Ensure you’re calling the correct endpoint (/tasks).
405 Method Not Allowed	Verify HTTP method (GET/POST/PUT/DELETE).
500 Internal Server Error	Check .env DB credentials and database connection.
CORS Issues	Already handled by Http::setupCors() in Support/Http.php.
Port in Use (8080)	Stop other processes or run: fuser -k 8080/tcp



Assumptions

Project is part of a technical assessment — the focus is clean, maintainable native PHP code.
No authentication or pagination (out of scope).
All responses are JSON.
Simple schema (tasks table only).
Designed to integrate easily with the Vue.js frontend (via /api proxy).


Summary

Pure Core PHP — no frameworks.
Organized codebase: Router, Controller, Service, Repository.
Secure PDO implementation.
Fully functional REST API with CRUD operations.
Easily extendable for production-level structure.



---

## Frontend (Vue.js)

This is the **frontend** part of the Mini Task Manager project, built using **Vue 3**, **Vite**, **Axios**, and **vuedraggable**.  
It communicates with the backend Core PHP API to display, add, update, and delete tasks in real time.

---

## Tech Stack
- **Vue 3** (Composition API)  
- **Vite** (for fast development build)  
- **Axios** (for API communication)  
- **vuedraggable** (for drag & drop between task lists)

---

## Project Structure


frontend/
├─ src/
│ ├─ api.js # Axios instance (baseURL: /api)
│ ├─ App.vue # Main component
│ ├─ main.js # Vue app bootstrap
│ ├─ assets/ # Global styles
│ └─ components/ # Task components (optional)
├─ package.json
├─ vite.config.js # Includes API proxy
└─ index.html


---

##  Setup & Installation

###  Navigate to frontend folder
```bash
cd frontend


npm install
npm run dev
http://127.0.0.1:5173


Backend Proxy Configuration

To connect the frontend with the backend API, a proxy is configured inside vite.config.js:
server: {
  host: '127.0.0.1',
  port: 5173,
  proxy: {
    '/api': {
      target: 'http://127.0.0.1:8080',
      changeOrigin: true,
      rewrite: p => p.replace(/^\/api/, '')
    }
  }
}
