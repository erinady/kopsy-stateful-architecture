# KopSy - Stateful Architecture & Data Persistence

## About KopSy
KopSy is a cloud-based cooperative management web application developed as part of Module 5: Stateful Architecture & Data Persistence for the Pengembangan Perangkat Lunak Berbasis Cloud course.

This project focuses on implementing stateful application architecture by integrating managed database services (DBaaS) and persistent data storage within a modern web application environment. The application is built using the Laravel framework and Inertia to support scalable and persistent cooperative data management.

## Module Focus
This module emphasizes:
- Stateful application architecture
- Data persistence implementation
- Managed Database (DBaaS) integration
- Persistent storage management in cloud environments
- Web application deployment and cloud-based data handling

## Features
- User authentication and authorization
- Cooperative member management
- Savings management
- Murabahah financing management
- Murabahah installment tracking
- Persistent data storage using managed database services
- Stateful session and application data management

## Technology Stack
- Laravel
- Inertia.js
- PostgreSQL
- Tailwind CSS
- Cloud-based managed database service (DBaaS)

## Course Information
This repository was developed for the course:
**Pengembangan Perangkat Lunak Berbasis Cloud**

Module:
**Modul Praktikum 5 — Stateful Architecture & Data Persistence**

## Installation
To set up the kopsy application locally, follow these steps:
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/e-kswp.git
   ```
2. Navigate to the project directory:
   ```bash
   cd e-kswp
    ```
3. Install the dependencies using Composer:
    ```bash
    composer install
    ```
4. Copy the example environment file and configure it:
    ```bash
    cp .env.example .env
    ```
    Update the `.env` file with your database and other configuration settings.
5. Generate an application key:
    ```bash
    php artisan key:generate
    ```
6. Run the database migrations:
    ```bash
    php artisan migrate
    ```
7. Run the database seeders to populate initial data:
    ```bash
    php artisan db:seed
    ```
8. Start the development server:
    ```bash
    php artisan serve
    npm run dev
    ```

## Copyright
This project is developed and maintained by Final Project Team KoTA-203 2026. All rights reserved.
[Team Members]
- Alanna Tanisya Anwar (231511034)
- Dhira Ramadini (231511041)
- Erina Dwi Yanti (231511043)
