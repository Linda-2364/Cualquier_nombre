# Cualquier_nombre
El Gestor de Órdenes de Trabajo y Mantenimiento es una aplicación web desarrollada con React, Node.js y SQL Server, orientada a empresas que necesitan organizar sus actividades de mantenimiento preventivo y correctivo. El sistema permite crear órdenes de trabajo, asignarlas a técnicos, registrar los repuestos utilizados y el tiempo invertido, y hacer seguimiento del estado de avance en tiempo real. La base de datos centraliza toda esta información a través de stored procedures que gestionan el ciclo de vida de cada orden y vistas SQL que generan reportes de órdenes pendientes por técnico y consumo de repuestos por período, eliminando el uso de registros manuales en papel o planillas dispersas.

ESTRUCTURA INICIAL DE CARPETAS
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
