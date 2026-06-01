# web-pruebas

Ambiente de pruebas de CENGICURSOS desplegable en Vercel y conectado a Supabase.

## Variables de entorno

Configura estas variables en Vercel:

- `SUPABASE_URL`: `https://nuhagigmrmgzaqeiqzhv.supabase.co`
- `SUPABASE_PUBLISHABLE_KEY`: publishable key del proyecto Supabase de pruebas

## Base de datos

El esquema PostgreSQL para Supabase esta en:

- `supabase/schema.sql`

La migracion completa con datos se genera localmente como
`supabase/cengi_cursos_postgres.sql`, pero no se sube al repositorio porque
contiene datos personales del dump MySQL.

Este ambiente de pruebas solo debe usar catalogos copiados desde produccion:
`ingenios`, `categorias_cursos`, `grado_academico` y `cursos`.
