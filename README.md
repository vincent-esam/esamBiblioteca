# ESAM Biblioteca PHP para XAMPP

## Carpeta lista para `htdocs`

Esta carpeta ya está preparada para copiarse dentro de:

```text
C:\xampp\htdocs\
```

Puedes dejarla así:

```text
C:\xampp\htdocs\biblioteca-esam-php
```

## Cómo ejecutarlo

1. Copia la carpeta `biblioteca-esam-php` completa dentro de `htdocs`.
2. Abre el panel de XAMPP.
3. Inicia `Apache`.
4. En tu navegador entra a:

```text
http://localhost/biblioteca-esam-php/
```

## Qué incluye

- `index.php`: landing con registro e inicio de sesión
- `panel.php`: panel protegido después del login
- `logout.php`: cierre de sesión
- `php/includes/auth.php`: lógica de usuarios y sesiones
- `assets/`: estilos y validación en navegador
- `storage/users.json`: almacenamiento temporal de usuarios

## Nota

Por ahora los usuarios se guardan en `storage/users.json`, así que no necesitas MySQL todavía para probarlo en XAMPP.
