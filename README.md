# web

Proyecto PHP desplegable en Vercel y conectado a Supabase.

## Variables de entorno

Configura estas variables en Vercel:

- `SUPABASE_URL`: `https://nccwvlopsqsctutopiks.supabase.co`
- `SUPABASE_PUBLISHABLE_KEY`: publishable key del proyecto Supabase

## Base de datos

El esquema PostgreSQL para Supabase está en:

- `supabase/schema.sql`

La migración completa con datos se genera localmente como
`supabase/cengi_cursos_postgres.sql`, pero no se sube al repositorio porque
contiene datos personales del dump MySQL.
