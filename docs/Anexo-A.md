# ANEXO A. Comparacion entre Laravel y Django

## A.1. Introduccion

El desarrollo de aplicaciones web requiere utilizar herramientas que faciliten la organizacion del codigo, la conexion con bases de datos, la administracion de rutas y la creacion de servicios web. Entre los frameworks mas utilizados se encuentran Laravel y Django.

Laravel es un framework de desarrollo web que utiliza PHP, mientras que Django utiliza Python. Ambos permiten crear aplicaciones web estructuradas, seguras y faciles de mantener.

El proposito de este anexo es comparar las principales caracteristicas de ambos frameworks y relacionarlas con el desarrollo de la API del proyecto Casa Horizonte.

## A.2. Comparacion de caracteristicas

| Caracteristica | Laravel | Django |
|---|---|---|
| Lenguaje de programacion | PHP | Python |
| Arquitectura | Modelo-Vista-Controlador (MVC) | Modelo-Vista-Plantilla (MVT) |
| Base de datos | MySQL, PostgreSQL, SQLite, entre otras | PostgreSQL, MySQL, SQLite, entre otras |
| Acceso a datos | Eloquent ORM | Django ORM |
| Administracion de rutas | Sistema de rutas de Laravel | Sistema de URL patterns |
| Desarrollo de API REST | Laravel API Resources y controladores | Django REST Framework |
| Seguridad | Proteccion CSRF, validacion, autenticacion y autorizacion | Proteccion CSRF, autenticacion, permisos y validacion |
| Plantillas | Blade | Django Template Language |

## A.3. Ventajas de Laravel

- Utiliza una estructura organizada que facilita el mantenimiento del codigo.
- Cuenta con Eloquent ORM para trabajar con bases de datos mediante modelos.
- Permite crear API REST utilizando rutas, controladores y API Resources.
- Facilita la validacion de datos y la gestion de solicitudes HTTP.
- Incluye herramientas para autenticacion y proteccion de aplicaciones.

## A.4. Ventajas de Django

- Utiliza Python, un lenguaje conocido por su sintaxis sencilla y legible.
- Incluye un sistema de administracion que facilita la gestion de informacion.
- Cuenta con un ORM para interactuar con diferentes motores de bases de datos.
- Permite desarrollar API REST mediante Django REST Framework.
- Proporciona herramientas de seguridad integradas.

## A.5. Similitudes y diferencias

Ambos frameworks permiten desarrollar aplicaciones web, organizar el codigo mediante una arquitectura definida, trabajar con bases de datos y gestionar solicitudes HTTP.

La principal diferencia se encuentra en los lenguajes de programacion y en la forma de organizar sus componentes. Laravel utiliza PHP y sigue el patron MVC, mientras que Django utiliza Python y aplica el patron MVT.

Tambien existen diferencias en sus herramientas para transformar los datos. Laravel utiliza API Resources para definir la informacion que se devuelve en las respuestas JSON, mientras que Django REST Framework utiliza serializers para cumplir una funcion similar.

## A.6. Aplicacion al proyecto Casa Horizonte

Para el desarrollo del proyecto Casa Horizonte se utiliza Laravel, debido a que permite administrar los recursos del sistema mediante modelos, controladores y rutas API.

En esta practica se implementaron los recursos RoomResource y ReservationResource, encargados de transformar la informacion de habitaciones y reservaciones en respuestas JSON.

Tambien se utilizo whenLoaded() para incluir informacion relacionada unicamente cuando la relacion correspondiente ha sido cargada. Esto permite controlar mejor la informacion que devuelve la API y evitar consultas innecesarias.

Aunque Django tambien permite desarrollar funcionalidades similares mediante Django REST Framework, Laravel proporciona las herramientas necesarias para implementar los requerimientos establecidos en esta practica.

## A.7. Conclusion

Laravel y Django son frameworks que ofrecen herramientas para desarrollar aplicaciones web seguras, organizadas y escalables. Ambos permiten trabajar con bases de datos, administrar rutas y construir servicios API.

Laravel facilita la transformacion de modelos en respuestas JSON mediante API Resources, mientras que Django utiliza serializers mediante Django REST Framework.

Para el proyecto Casa Horizonte, Laravel resulta adecuado porque permite implementar los recursos de habitaciones y reservaciones, controlar las relaciones entre modelos y presentar la informacion de manera estructurada mediante una API REST.