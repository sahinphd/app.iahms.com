# 🚀 LARAVEL LMS + GOOGLE CLOUD STORAGE (STREAMING) – MASTER AI PROMPT

## 🎯 Objective

Build a **production-ready Learning Management System (LMS)** using Laravel (PHP) with:

* Role-based system (Admin, Teacher, Student)
* Google Cloud Storage for file storage
* Secure video streaming using signed URLs
* Low-cost hosting compatibility

---

# 🧠 Core Requirements

## 🔧 Tech Stack

* Backend: Laravel (latest version)
* Frontend: Blade + Tailwind CSS
* Database: MySQL
* Cloud Storage: Google Cloud Storage
* Authentication: Laravel built-in auth (session-based)

---

# 🏗️ System Architecture

* Laravel handles:

  * Authentication
  * Business logic
  * Access control
* Google Cloud handles:

  * Video storage
  * File storage
  * Streaming delivery

---

# 👥 User Roles

## Admin

* Manage users
* Manage courses
* Assign teachers

## Teacher

* Create courses
* Upload lectures (videos to GCP)
* Upload study materials

## Student

* Enroll in courses
* Watch lectures
* Download materials

---

# 🔐 Authentication System

* Laravel authentication (login/register)
* Password hashing
* Role-based middleware:

  * admin
  * teacher
  * student

---

# 📚 Course Management

Create full CRUD system:

Fields:

* title
* description
* thumbnail
* teacher_id

Include:

* Modules (chapters)
* Course publish/unpublish

---

# 🎥 VIDEO STORAGE & STREAMING (CRITICAL)

## Requirements:

* Upload videos to Google Cloud Storage
* Store only file path in database
* Do NOT store videos locally
* Do NOT make bucket public

---

## 🔐 Secure Streaming Logic

When a student clicks a video:

1. Check:

   * Is user logged in?
   * Is user enrolled in course?
2. Generate **signed URL**
3. Return URL to frontend
4. Stream video directly from GCP

---

## ⚙️ Laravel Implementation Requirements

### Install SDK:

composer require google/cloud-storage

---

### ENV Configuration:

* GCP_PROJECT_ID
* GCP_BUCKET
* GOOGLE_APPLICATION_CREDENTIALS (path to JSON key)

---

### Create Service Class:

Create a service class:

* Upload file to GCP bucket
* Generate signed URL
* Delete file

---

### Upload Logic:

* Upload video to:
  /videos/course-id/

* Upload materials to:
  /materials/course-id/

---

### Streaming Logic:

* Generate signed URL with expiry (15–30 minutes)
* Return JSON response:
  { video_url: "signed_url_here" }

---

# 📂 Study Material System

* Upload PDF/DOC/PPT
* Store in GCP
* Secure download via signed URL

---

# 📅 Live Class System

* Schedule classes:

  * title
  * datetime
  * Google Meet link
* Students can join via dashboard

---

# 🧩 Enrollment System

* Student enrolls in course
* Only enrolled users can:

  * Watch videos
  * Access materials

---

# 📊 Dashboard System

## Admin:

* Total users
* Total courses

## Teacher:

* Courses created
* Lectures uploaded

## Student:

* Enrolled courses
* Upcoming classes

---

# 🗄️ Database Schema

Tables:

* users (id, name, email, password, role)
* courses (id, title, description, teacher_id)
* modules (id, course_id, title)
* lectures (id, module_id, title, file_path)
* materials (id, module_id, file_path)
* enrollments (id, student_id, course_id)
* live_classes (id, course_id, title, datetime, link)

Use foreign keys and relationships.

---

# 🔒 Security Requirements

* Validate all inputs
* Protect routes using middleware
* Restrict file access via backend only
* Signed URLs for all file access
* No public bucket access

---

# 🎨 UI Requirements

* Clean dashboard layout
* Sidebar navigation
* Mobile responsive
* Use Tailwind CSS
* Pages:

  * Login/Register
  * Dashboard
  * Courses
  * Video player
  * Admin panel

---

# 📦 Output Requirements

Generate:

1. Full Laravel project structure
2. Migration files
3. Controllers
4. Middleware
5. Service class for GCP
6. Blade views
7. Routes (web.php)
8. .env example
9. Setup instructions

---

# 🚀 Deployment Readiness

* Compatible with shared hosting
* Works with Apache/Nginx
* Uses environment variables
* Optimized for low-cost hosting

---

# ⚡ Important Instructions

* Write clean, modular, production-ready code
* Avoid dummy data
* Use proper naming conventions
* Follow Laravel best practices

---

# 🎯 Final Goal

Deliver a **fully functional LMS system** where:

* Teachers upload videos → stored in GCP
* Students click → secure streaming via signed URL
* System is scalable and production-ready



👉 “Generate backend first”

👉 then generate Blade UI”
Then:
👉 “Add GCP integration service class”