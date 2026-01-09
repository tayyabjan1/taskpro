# Projects Module - Documentation

## Overview
The Projects module allows administrators to create, manage, and track projects with assigned employees, deadlines, and status updates.

## Features

### 1. **Project Management**
- Create new projects with detailed information
- Edit existing projects
- Delete projects
- View all projects in a table format

### 2. **Project Fields**
- **Name**: Project title/name
- **Description**: Detailed project description
- **Assigned To**: Employee assigned to the project
- **Start Date**: Project start date
- **Deadline**: Project completion deadline
- **Status**: Current project status
  - Planning
  - In Progress
  - Completed
  - On Hold

### 3. **Sidebar Integration**
- Recent 5 projects displayed in the sidebar
- Color-coded status indicators:
  - 🟠 Orange: Planning
  - 🔵 Blue: In Progress
  - 🟢 Green: Completed
  - 🔴 Red: On Hold
- Quick access to edit projects from sidebar
- "View All Projects" link to see complete list

## Files Created

### Database
- `projects_table.sql` - Database schema for projects table

### Models
- `app/Model/Project.php` - Project model with CRUD functions

### Pages
- `projects.php` - View all projects
- `create_project.php` - Create new project form
- `edit-project.php` - Edit existing project
- `delete-project.php` - Delete project handler

### Backend Handlers
- `app/add-project.php` - Add project handler
- `app/update-project.php` - Update project handler

### Updated Files
- `inc/nav.php` - Added Projects link and sidebar section
- `css/style.css` - Added project-related styles

## Usage

### For Administrators:

1. **Access Projects**
   - Click "Projects" in the sidebar navigation
   - Or click "View All Projects" at the bottom of the recent projects list

2. **Create a Project**
   - Click "Add Project" button on the projects page
   - Fill in project details
   - Assign to an employee (optional)
   - Set start date and deadline (optional)
   - Select project status
   - Click "Create Project"

3. **Edit a Project**
   - Click "Edit" next to any project in the table
   - Or click on a project name in the sidebar
   - Update the desired fields
   - Click "Update Project"

4. **Delete a Project**
   - Click "Delete" next to any project in the table
   - Project will be permanently removed

5. **View Project Status**
   - Projects are color-coded by status in the table
   - Sidebar shows status indicators for quick reference

## Database Structure

```sql
CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('planning','in_progress','completed','on_hold') DEFAULT 'planning',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)
);
```

## Sample Projects
Three sample projects have been added to demonstrate the feature:
1. Website Redesign (In Progress)
2. Mobile App Development (Planning)
3. Database Migration (In Progress)

## Navigation Structure

**Admin Sidebar:**
- Dashboard
- Projects ← NEW
- Logout
- Recent Projects (sidebar section) ← NEW

## Future Enhancements (Optional)
- Project tasks/subtasks
- Project progress tracking
- File attachments
- Project comments/notes
- Project timeline view
- Employee project dashboard
