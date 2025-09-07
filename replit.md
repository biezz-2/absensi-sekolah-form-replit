# Overview

This is a student attendance system using QR codes (Sistem Absensi QR Siswa) built as a monorepo with separate frontend and backend applications. The system appears to handle student check-ins via QR code scanning with location-based verification. It's designed for production deployment with considerations for camera access and geolocation features that require HTTPS.

# User Preferences

Preferred communication style: Simple, everyday language.

# System Architecture

## Monorepo Structure
The project uses a monorepo approach with clearly separated frontend and backend applications, orchestrated through Docker Compose for development and deployment.

## Frontend Architecture
- **Framework**: Next.js 15.5.2 with React 19.1.0
- **Styling**: TailwindCSS with custom dark mode support and component variants
- **UI Components**: Custom component library with reusable elements (dropdowns, buttons, layouts)
- **State Management**: React Context for sidebar state and theme management
- **Routing**: App Router architecture with TypeScript support
- **Build Tool**: Next.js with Turbopack for faster development builds

The frontend includes specialized components for QR scanning functionality and admin statistics dashboard, indicating the core attendance management features.

## Backend Architecture
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Asset Pipeline**: Vite with Laravel plugin for modern frontend asset compilation
- **Styling**: TailwindCSS 4.0 integrated with Laravel
- **Development Tools**: Laravel Pail for log management, Laravel Pint for code formatting

The backend follows Laravel's conventional MVC architecture with API endpoints exposed for the frontend consumption.

## Development Environment
- **Containerization**: Docker Compose orchestrates the entire stack
- **Reverse Proxy**: Nginx handles routing between frontend and backend services
- **Hot Reload**: Both frontend (Next.js) and backend (Laravel) support development hot reloading
- **Port Configuration**: Frontend on 5000, proxied through port 8080, with API endpoints at /api/*

## Cross-Application Communication
- **API Integration**: Frontend communicates with Laravel backend via proxied /api routes
- **Health Checking**: Built-in health check endpoints for service monitoring
- **CORS**: Configured for cross-origin requests between services

## Production Considerations
- **Security Requirements**: Camera and geolocation features require HTTPS in production
- **Environment Management**: Separate .env files for frontend and backend with example templates
- **Asset Optimization**: Vite build process for optimized production assets

## External Service Integration
- **Email**: Mailhog configured for development email testing (port 8025)
- **Metrics**: Soketi metrics available on port 9601
- **Real-time Features**: WebSocket capability suggested by Soketi inclusion

# External Dependencies

## Core Frameworks
- **Laravel Framework**: Web application framework with full MVC capabilities
- **Next.js**: React-based frontend framework with server-side rendering
- **React**: JavaScript library for building user interfaces

## Development Tools
- **Docker & Docker Compose**: Containerization and orchestration
- **Nginx**: Reverse proxy and static file serving
- **Vite**: Modern build tool for frontend assets
- **TailwindCSS**: Utility-first CSS framework

## PHP Dependencies
- **Guzzle HTTP**: HTTP client library for external API calls
- **Carbon**: Date manipulation library
- **Faker**: Test data generation
- **PHPUnit**: Testing framework

## JavaScript Dependencies
- **ApexCharts**: Data visualization and charting
- **Flatpickr**: Date picker component
- **JSVectorMap**: Interactive map components
- **Day.js**: Date utility library

## Development Services
- **Mailhog**: Email testing in development
- **Soketi**: WebSocket server for real-time features

## Infrastructure Requirements
- **PHP 8.2+**: Required for Laravel 12
- **Node.js**: Required for Next.js and build tools
- **Camera Access**: For QR code scanning functionality
- **Geolocation**: For location-based attendance verification