#!/bin/sh
# Gradle wrapper batch script
@if "%DEBUG%"=="" @echo off
@local set DIRNAME=%~dp0
@if "%DIRNAME%"=="" set DIRNAME=.
@local set APP_HOME=%DIRNAME%
@if exist "%JAVA_HOME%\bin\java.exe" (
    set JAVACMD=%JAVA_HOME%\bin\java.exe
) else (
    set JAVACMD=java
)
"%JAVACMD%" -classpath "%APP_HOME%\gradle\wrapper\gradle-wrapper.jar" org.gradle.wrapper.GradleWrapperMain %*
