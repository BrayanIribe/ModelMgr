# ModelMgr

<p align="center">
  <img src="https://user-images.githubusercontent.com/520683/62002257-10dd0480-b0b5-11e9-8e17-cd22fcc6ac57.png" alt="ModelMgr"/>
</p>

Model manager (ModelMgr) is a tool for Phalcon (at this moment). It generates models from a certain schemas. **It can update their properties only**, remove the suffixes of models, etc.

This application was developed by Brayan Iribe. It was made in order to reduce the hassle of type every table and model name in the command line.

**This tool requires [Phalcon Devtools](https://github.com/phalcon/phalcon-devtools) in order to work.**

**Only works with MySQL**

**¿Español? Desliza hacia abajo.**

### INSTALLATION

In bash run:

```bash

> composer global require ivybridge/model-mgr
#now modelmgr will be available
> modelmgr

```

### COMMANDS

<p align="center">
  <img src="https://user-images.githubusercontent.com/520683/62002137-10dc0500-b0b3-11e9-8088-8ae2d4f384da.png" alt="ModelMgr"/>
</p>

If I have a table with the suffix for example: **sat_impuestos**, where _sat_ is the suffix, ModelMgr
can or not keep it.

If I don't use the argument **--keep-suffix=sat**, the suffix will be deleted and the final model will be
called **Impuestos**.

You can update only one model by passing its table name to ModelMgr, for example:

```bash

> modelmgr sat_impuestos

```

By doing this, if the model exists in the project and the table name is correct, the model **will be updated only**. The namespace is not required in this case, because you have provided the model already.

If you don't provide a model to update, **all existant models will be updated, and the non-existant will be created**.

<p align="center">
  <img src="https://user-images.githubusercontent.com/520683/62002098-7976b200-b0b2-11e9-9643-60ecfd6daa4e.png" alt="ModelMgr"/>
</p>

**GREEN:** You can put whatever you want in those lines, ModelMgr can handle it and if the model is updated, it will keep it.

**RED:** You **CANNOT** put comments, functions or newlines in that block. **ModelMgr cannot handle it and will result in broken model**.

**YELLOW:** You can put whatever you want, ~~as long as you don't use ModelMgr code injection. If you do so, the model can likely result broken with duplicated code~~.

**ModelMgr cannot detect if you added a comment, functions or new lines in the model props. Please dont do so. Keep the model as Phalcon generates it originally. If you add them, the model will likely result broken. You can comment an do whatever you want in the functions.**

**ModelMgr will use the database settings of config.php**

At this moment, ModelMgr works only for Phalcon Project. However, I'm interested porting it to other frameworks, like Laravel.

# Español

Model manager (ModelMgr) es una herramienta desarrollada para Phalcon. Genera modelos a partir de esquemas, puede actualizar las propiedades de los modelos solamente (conservando sus funciones originales), remover sufijos de tablas, etcétera.

Esta aplicación fue desarrollada por Brayan Iribe con el fin de reducir las molestias de escribir en la línea de comandos modelo por modelo y actualizar su contenido uno por uno.

**Esta herramienta requiere de las [Phalcon Devtools](https://github.com/phalcon/phalcon-devtools) para funcionar.**

**Solo trabaja con MySQL**

### INSTALACIÓN

En tu proyecto de Phalcon ejecuta:

```bash

> composer global require ivybridge/model-mgr
#ahora modelmgr debe estar disponible
> modelmgr

```

### COMANDOS

<p align="center">
  <img src="https://user-images.githubusercontent.com/520683/62002137-10dc0500-b0b3-11e9-8088-8ae2d4f384da.png" alt="ModelMgr"/>
</p>

Si tengo una tabla con un sufijo por ejemplo: **sat_impuestos**, donde _sat_ es el sufijo, ModelMgr
puede o no conservar el mismo.

Si no se utiliza el comando **--keep-sufix=sat**, el sufijo se eliminará y el modelo terminará llamandose
**Impuestos**.

Puedes actualizar las propiedades de un modelo simplemente pasando su nombre a ModelMgr, por ejemplo:

```bash

> modelmgr sat_impuestos

```

Al hacer esto, si el modelo existe en el proyecto y el nombre de la tabla es el correcto, se actualizará **solamente** dicho modelo. No es necesario pasar el **namespace**, ya que al tratarse de un solo modelo, se obtiene el namespace del mismo.

Si no se proporciona un modelo a actualizar, entonces **se crearán aquellos modelos que no existan en el momento, y se actualizarán aquellos que si existan**.

<p align="center">
  <img src="https://user-images.githubusercontent.com/520683/62002098-7976b200-b0b2-11e9-9643-60ecfd6daa4e.png" alt="ModelMgr"/>
</p>

**VERDE:** Puedes poner lo que quieras en esas líneas, ModelMgr puede controlarlo. Si el modelo es actualizado, lo mantendrá ahí mismo.

**ROJO:** **NO PUEDES** poner comentarios, funciones o nuevas líneas en ese bloque. **ModelMgr no podrá controlarlo y devolverá un modelo probablemente roto**.

**AMARILLO:** Puedes poner lo que quieras, ~~siempre y cuando no utilices la inyección de código de ModelMgr. Si lo haces, el modelo puede resultar roto o con código duplicado~~.

**ModelMgr no puede detectar que agregaste un comentario, funciones o nuevas líneas en las propiedades del modelo. Mantén el modelo como Phalcon lo genera originalmente. Si haces esto, el modelo puede terminar probablemente roto**.

En este momento, ModelMgr solo funciona para proyectos Phalcon. Sin embargo, estoy interesado en poder portar esta herramienta a otros frameworks, como Laravel.
