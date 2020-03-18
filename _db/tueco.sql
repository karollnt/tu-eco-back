-- MySQL Script generated by MySQL Workbench
-- Wed Mar 18 09:42:37 2020
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema tueco
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema tueco
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `tueco` DEFAULT CHARACTER SET utf8 ;
USE `tueco` ;

-- -----------------------------------------------------
-- Table `tueco`.`tipo_identidad`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`tipo_identidad` ;

CREATE TABLE IF NOT EXISTS `tueco`.`tipo_identidad` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`perfil`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`perfil` ;

CREATE TABLE IF NOT EXISTS `tueco`.`perfil` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`usuario` ;

CREATE TABLE IF NOT EXISTS `tueco`.`usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  `apellido` VARCHAR(45) NOT NULL DEFAULT '',
  `identificacion` VARCHAR(45) NOT NULL DEFAULT '',
  `telefono` VARCHAR(45) NOT NULL DEFAULT '',
  `direccion` VARCHAR(45) NOT NULL DEFAULT '',
  `correo` VARCHAR(125) NOT NULL DEFAULT '',
  `foto` VARCHAR(45) NOT NULL DEFAULT '',
  `placa` VARCHAR(45) NOT NULL DEFAULT '',
  `clave` VARCHAR(45) NOT NULL DEFAULT '',
  `id_tipo_identidad` INT NOT NULL,
  `id_perfil` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_usuario_tipo_identidad_idx` (`id_tipo_identidad` ASC) VISIBLE,
  INDEX `fk_usuario_perfil1_idx` (`id_perfil` ASC) VISIBLE,
  CONSTRAINT `fk_usuario_tipo_identidad`
    FOREIGN KEY (`id_tipo_identidad`)
    REFERENCES `tueco`.`tipo_identidad` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_perfil1`
    FOREIGN KEY (`id_perfil`)
    REFERENCES `tueco`.`perfil` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`tipo_categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`tipo_categoria` ;

CREATE TABLE IF NOT EXISTS `tueco`.`tipo_categoria` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`medida`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`medida` ;

CREATE TABLE IF NOT EXISTS `tueco`.`medida` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NULL DEFAULT '',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`categoria`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`categoria` ;

CREATE TABLE IF NOT EXISTS `tueco`.`categoria` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  `precio` DECIMAL(10,2) NOT NULL,
  `id_tipo` INT NOT NULL,
  `id_medida` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_categoria_tipo_categoria1_idx` (`id_tipo` ASC) VISIBLE,
  INDEX `fk_categoria_medida1_idx` (`id_medida` ASC) VISIBLE,
  CONSTRAINT `fk_categoria_tipo_categoria1`
    FOREIGN KEY (`id_tipo`)
    REFERENCES `tueco`.`tipo_categoria` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_categoria_medida1`
    FOREIGN KEY (`id_medida`)
    REFERENCES `tueco`.`medida` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`departamento`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`departamento` ;

CREATE TABLE IF NOT EXISTS `tueco`.`departamento` (
  `iddepartamento` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`iddepartamento`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`ciudades`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`ciudades` ;

CREATE TABLE IF NOT EXISTS `tueco`.`ciudades` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(45) NOT NULL DEFAULT '',
  `id_departamento` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_ciudades_departamento1_idx` (`id_departamento` ASC) VISIBLE,
  CONSTRAINT `fk_ciudades_departamento1`
    FOREIGN KEY (`id_departamento`)
    REFERENCES `tueco`.`departamento` (`iddepartamento`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`solicitud`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`solicitud` ;

CREATE TABLE IF NOT EXISTS `tueco`.`solicitud` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_recogida` DATETIME NULL DEFAULT NULL,
  `id_solicitante` INT NOT NULL DEFAULT 0,
  `id_reciclatendero` INT NULL DEFAULT 0,
  `comentario` TEXT NOT NULL DEFAULT '',
  `ciudades_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_solicitud_usuario1_idx` (`id_solicitante` ASC) VISIBLE,
  INDEX `fk_solicitud_usuario2_idx` (`id_reciclatendero` ASC) VISIBLE,
  INDEX `fk_solicitud_ciudades1_idx` (`ciudades_id` ASC) VISIBLE,
  CONSTRAINT `fk_solicitud_usuario1`
    FOREIGN KEY (`id_solicitante`)
    REFERENCES `tueco`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_solicitud_usuario2`
    FOREIGN KEY (`id_reciclatendero`)
    REFERENCES `tueco`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_solicitud_ciudades1`
    FOREIGN KEY (`ciudades_id`)
    REFERENCES `tueco`.`ciudades` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`detalle_solicitud`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`detalle_solicitud` ;

CREATE TABLE IF NOT EXISTS `tueco`.`detalle_solicitud` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_solicitud` INT NOT NULL DEFAULT 0,
  `id_categoria` INT NOT NULL DEFAULT 0,
  `valor` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `cantidad` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_detalle_solicitud_solicitud1_idx` (`id_solicitud` ASC) VISIBLE,
  INDEX `fk_detalle_solicitud_categoria1_idx` (`id_categoria` ASC) VISIBLE,
  CONSTRAINT `fk_detalle_solicitud_solicitud1`
    FOREIGN KEY (`id_solicitud`)
    REFERENCES `tueco`.`solicitud` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_detalle_solicitud_categoria1`
    FOREIGN KEY (`id_categoria`)
    REFERENCES `tueco`.`categoria` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`ruta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`ruta` ;

CREATE TABLE IF NOT EXISTS `tueco`.`ruta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_reciclatendero` INT NOT NULL DEFAULT 0,
  `comentario` TEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  INDEX `fk_ruta_usuario1_idx` (`id_reciclatendero` ASC) VISIBLE,
  CONSTRAINT `fk_ruta_usuario1`
    FOREIGN KEY (`id_reciclatendero`)
    REFERENCES `tueco`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tueco`.`solicitudes_ruta`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tueco`.`solicitudes_ruta` ;

CREATE TABLE IF NOT EXISTS `tueco`.`solicitudes_ruta` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_ruta` INT NOT NULL DEFAULT 0,
  `id_solicitud` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_solicitudes_ruta_ruta1_idx` (`id_ruta` ASC) VISIBLE,
  INDEX `fk_solicitudes_ruta_solicitud1_idx` (`id_solicitud` ASC) VISIBLE,
  CONSTRAINT `fk_solicitudes_ruta_ruta1`
    FOREIGN KEY (`id_ruta`)
    REFERENCES `tueco`.`ruta` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_solicitudes_ruta_solicitud1`
    FOREIGN KEY (`id_solicitud`)
    REFERENCES `tueco`.`solicitud` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
