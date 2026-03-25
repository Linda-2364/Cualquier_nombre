# 🔧 Gestor de Órdenes de Trabajo y Mantenimiento

Sistema web para la planificación, asignación y seguimiento de órdenes de trabajo de mantenimiento preventivo y correctivo en entornos industriales y empresariales.

---

## 📋 Descripción

Esta aplicación permite gestionar el ciclo completo de una orden de trabajo: desde su creación y asignación a técnicos, hasta el registro de repuestos utilizados, tiempo invertido y cierre con estado final. Centraliza la información de mantenimiento en una única plataforma accesible para supervisores, técnicos y personal administrativo.

---

## 🎯 Objetivos de Base de Datos

- Diseñar un modelo relacional que gestione órdenes de trabajo, técnicos, repuestos y estados de avance.
- Implementar **Stored Procedures** para crear, asignar y cerrar órdenes de trabajo.
- Crear **Vistas SQL** que muestren órdenes pendientes por técnico y el consumo de repuestos por período.

---

## 🛠️ Tecnologías utilizadas

| Capa | Tecnología |
|------|-----------|
| Frontend | React |
| Backend | Node.js + Express |
| Base de datos | Microsoft SQL Server |
| ORM / Conexión | mssql (tedious) |
| Control de versiones | Git + GitHub |

---

## 📁 Estructura del proyecto

```
gestor-ordenes-mantenimiento/
│
├── database/
│   ├── migrations/        # Creación de tablas y relaciones
│   ├── procedures/        # Stored procedures (crear, asignar, cerrar órdenes)
│   ├── views/             # Vistas SQL (órdenes por técnico, consumo de repuestos)
│   └── seed.sql           # Datos de prueba
│
├── backend/
│   ├── config/            # Conexión a SQL Server
│   ├── routes/            # Endpoints REST
│   ├── controllers/       # Lógica de negocio
│   └── models/            # Consultas a la base de datos
│
├── frontend/
│   └── src/
│       ├── pages/         # Dashboard, Órdenes, Técnicos, Repuestos, Reportes
│       ├── components/    # Componentes reutilizables
│       └── services/      # Llamadas a la API
│
└── README.md
```

---

## ⚙️ Funcionalidades principales

- Crear y gestionar órdenes de trabajo (preventivo / correctivo)
- Asignar técnicos y registrar tiempo invertido por orden
- Controlar el uso de repuestos por orden y por período
- Consultar el estado de avance de cada orden en tiempo real
- Reportes de órdenes pendientes por técnico
- Reportes de consumo de repuestos por período

---

## 🗄️ Stored Procedures implementados

| Procedure | Descripción |
|-----------|-------------|
| `sp_crear_orden` | Registra una nueva orden de trabajo |
| `sp_asignar_tecnico` | Asigna un técnico a una orden existente |
| `sp_cerrar_orden` | Cierra una orden y registra el estado final |
| `sp_registrar_repuesto` | Añade un repuesto utilizado a una orden |

---

## 👁️ Vistas SQL

| Vista | Descripción |
|-------|-------------|
| `vw_ordenes_por_tecnico` | Órdenes pendientes agrupadas por técnico |
| `vw_consumo_repuestos` | Consumo de repuestos filtrado por período |
| `vw_ordenes_pendientes` | Resumen general de órdenes sin cerrar |

---

## 🚀 Instrucciones de instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/gestor-ordenes-mantenimiento.git
cd gestor-ordenes-mantenimiento
```

### 2. Configurar la base de datos
```bash
# Ejecutar en SQL Server Management Studio (SSMS) en este orden:
1. database/migrations/001_crear_tablas.sql
2. database/migrations/002_relaciones_fk.sql
3. database/procedures/
4. database/views/
5. database/seed.sql   # opcional, para datos de prueba
```

### 3. Configurar variables de entorno
```bash
cp .env.example .env
# Completar con los datos de conexión a SQL Server
```

```env
DB_SERVER=localhost
DB_NAME=gestor_ordenes
DB_USER=sa
DB_PASSWORD=tu_password
PORT=3000
```

### 4. Instalar dependencias e iniciar
```bash
# Backend
cd backend
npm install
npm run dev

# Frontend (en otra terminal)
cd frontend
npm install
npm run dev
```

---

## 👥 Roles del sistema

| Rol | Permisos |
|-----|---------|
| Administrador | Acceso total, configuración del sistema |
| Supervisor | Crear, asignar y cerrar órdenes |
| Técnico | Ver sus órdenes asignadas y actualizar estado |
| Almacén | Gestión de repuestos |

---

*Proyecto académico — Base de Datos Avanzada*
