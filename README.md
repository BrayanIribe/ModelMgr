# ModelMgr

<p align="center">
  <img src="https://user-images.githubusercontent.com/520683/61989813-909d9d00-afe9-11e9-9267-99841a8e5de1.png" alt="ModelMgr"/>
</p>

Model manager (ModelMgr) is a tool for Phalcon (at this moment). It generates models from a certain schemas. It can update their properties only, keep the suffixes of tables, etc.

This application was developed by Brayan Iribe. It was made in order to reduce the hassle of type every table and model name in the command line.

**ModelMgr cannot detect if you added a comment in the props or at the top of script. Please dont use comments or new lines, keep the model as Phalcon generates it originally. If you do so, the updated model will be bad! You can comment an do whatever you want in the functions.**

**This tool requires [Phalcon Devtools](https://github.com/phalcon/phalcon-devtools) in order to work.**

**Only works with MySQL**

**¿Español? Desliza hacia abajo.**

### INSTALLATION

In your Phalcon project run:

```bash

git clone https://github.com/BrayanIribe/ModelMgr.git
move ModelMgr\modelmgr.bat modelmgr.bat
modelmgr

```

**ModelMgr is intented to be inside a directory of the project root.**

### COMMANDS

```bash

 Available commands:

 --update Update model properties only, keep the original functions
 --keep-suffix=suf1,suf2,suf3 Keeps the suffix of the table of model.
 --db=dbname1,dbname2 Generate models from certain schemas.
 --namespace=App\\Models Namespace of the generated models. Default: App.
 --help prints help screen.

```

If I have a table with the suffix for example: **sat_impuestos**, where _sat_ is the suffix, ModelMgr
can or not keep it.

If you don't use the command **--keep-suffix**, the suffix will be deleted and the final model will be
called **Impuestos**.

**ModelMgr will use the database settings of /app/config/config.php**

# Español

Model manager (ModelMgr) es una herramienta desarrollada para Phalcon. Genera modelos a partir de esquemas, puede actualizar las propiedades de los modelos solamente (conservando sus funciones originales), mantener sufijos de tablas etcétera.

Esta aplicación fue desarrollada por Brayan Iribe con el fin de reducir las molestias de escribir en la línea de comandos modelo por modelo y actualizar su contenido uno por uno.

**ModelMgr no puede detectar que añadiste un comentario en las propiedades o al inicio del script. Por favor no uses comentarios o nuevas líneas, manten el modelo como Phalcon lo genera automáticamente. Si haces eso, el modelo actualizado se generará mal. Puedes comentar y hacer lo que sea en las funciones.**

**Esta herramienta requiere de las [Phalcon Devtools](https://github.com/phalcon/phalcon-devtools) para funcionar.**

### INSTALACIÓN

En tu proyecto de Phalcon ejecuta:

```bash

#WINDOWS

git clone https://github.com/BrayanIribe/ModelMgr.git
move ModelMgr\modelmgr.bat modelmgr.bat
modelmgr

```

**ModelMgr está hecho para estar en la raíz del proyecto dentro de un directorio.**

### COMANDOS

```bash

 Comandos disponibles:

 --update Actualiza las propiedades del modelo solamente, conserva las funciones originales.
 --keep-suffix=suf1,suf2,suf3 Mantiene el sufijo de las tablas.
 --db=dbname1,dbname2 Genera modelos a partir de determinados esquemas.
 --namespace=App\\Models Namespace de los modelos. Por defecto: App.
 --help Imprime pantalla de ayuda.

```

Si tengo una tabla con un sufijo por ejemplo: **sat_impuestos**, donde _sat_ es el sufijo, ModelMgr
puede o no conservar el mismo.

Si no se utiliza el comando **--keep-sufix**, el sufijo se eliminará y el modelo terminará llamandose
**Impuestos**.

**Solo trabaja con MySQL**

**ModelMgr utilizará la configuración de la base de datos de /app/config/config.php**
