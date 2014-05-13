Universal-Installation-PHP
==========================

Universal Installation PHP is the ultimate tool to install PHP scripts in languages ​​Multiple blocks, a secilla and elegant.

![Alt Universal Installation PHP 1](images/screenshot1.png)

![Alt Universal Installation PHP 2](images/screenshot2.png)

![Alt Universal Installation PHP 3](images/screenshot3.png)

![Alt Universal Installation PHP 4](images/screenshot4.png)

Installation
------------

Unzip the file, leaving the installation directory included in your project. 
To run indicate the url in your browser: http://localhost/my_proyect/installation

Features
--------

<ol>
<li>Very easy to use and configure</li>
<li>Design based on Bootstrap 3</li>
<li>Multiple languages</li>
<li>Installation Overview</li>
<li>Creating log file with error trapping</li>
</ol>

How to use
----------

Puede personalizar su script de instalación editando el archivo <b>setting.xml</b>
<code>
<?xml version="1.0" encoding="UTF-8"?>
<install>
    <title>Universal Installation PHP</title>
    <copyright>Universal Installation PHP - 2014 - Basilio Fajardo Gálvez</copyright>
    <source>data.sql</source>
    <requires>
        <version>5.1.2</version>
        <extension name="curl" />
        <extension name="gd" />
        <extension name="mbstring" />
        <extension name="mcrypt" />
        <extension name="simplexml" />
        <extension name="zip" />
        <extension name="json" />
    </requires>
    <languages>
        <default>es</default>
        <language id="es">
            <choose title="Idioma">
                <option value="es">Español</option>
                <option value="en">Inglés</option>
            </choose>
        </language>
        <language id="en">
            <choose title="Language">
                <option value="es">Spanish</option>
                <option value="en">English</option>
            </choose>
        </language>
    </languages>
    <values>
        <host>localhost</host>
        <database></database>
        <username></username>
        <prefix>uiphp</prefix>
    </values>
</install>
</code>

