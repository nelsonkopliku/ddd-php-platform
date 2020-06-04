<p align="center">
    <img src="https://avatars2.githubusercontent.com/u/8167114?s=460&u=803205cb3eebb610143d8e446adc74b0a60362be&v=4" width="192px" height="192px"/>
</p>

# DDD Platform

Architecture for a Domain Driven Design oriented application 

## Table of Contents

* [🚀Environment setup](#environment-setup)
  * [🐳Needed tools](#needed-tools)
  * [🛠️Environment configuration](#environment-configuration)
  * [🌍Application execution](#application-execution)
  * [✅Tests execution](#tests-execution)
* [Project explanation](#project-explanation)
  * [Bounded Contexts](#bounded-contexts)
  * [Hexagonal Architecture](#hexagonal-architecture)
* [🤩More](#more)

## Environment setup

### Needed tools

1. [Docker](https://docs.docker.com/get-docker/)
2. [Docker Compose](https://docs.docker.com/compose/install/)

### Environment configuration

Create a local environment file if needed: `cp ./app/.env ./app/.env.local`

No strict need to do this manually, if you run `make start` it will be automatically prepared for you.

Please note: Make targets will run `cp -n ./app/.env.dist ./app/.env`, so if a `.env` file is already present, it won't be overwritten

### Application execution

To start the platform running `make start` should be enough.

It will start the containerized platform:

```
$ docker-compose ps
             Name                            Command                  State      
---------------------------------------------------------------------------------
          Name                        Command                  State                                                 Ports                                          
--------------------------------------------------------------------------------------------------------------------------------------------------------------------
demo-platform_adminer_1    entrypoint.sh docker-php-e ...   Up             0.0.0.0:5555->8080/tcp                                                                   
demo-platform_app_1        docker-entrypoint php-fpm        Up (healthy)   9000/tcp                                                                                 
demo-platform_db_1         docker-entrypoint.sh --def ...   Up             0.0.0.0:3306->3306/tcp, 33060/tcp                                                        
demo-platform_gateway_1    docker-entrypoint                Up             0.0.0.0:8080->80/tcp                                                                     
demo-platform_rabbitmq_1   docker-entrypoint.sh rabbi ...   Up             15671/tcp, 0.0.0.0:8083->15672/tcp, 25672/tcp, 4369/tcp, 5671/tcp, 0.0.0.0:5672->5672/tcp
```

Take a look at [Makefile](Makefile) to see what's going on
```
$ make
Usage:
 make [target]

Available targets:
 help             Help
 start            starts platform
 stop             stops running platform `docker-compose stop`
 build            starts platform `docker-compose up -d --build`
 destroy          destroys platform `docker-compose down --remove-orphans`
 show-routes      Show routes exposed by the platform
 code-static-analysis Run PHPStan to find errors in code.
 checkout-tests   Run Checkout tests
 ...
```

### Tests execution

1. `make checkout-tests` will run Checkout BC test suite
2. More to come...

## Project explanation

This project's and this Architecture's goal is to provide a solid foundation for Acmes's evolution.

- focus on business needs
- provide valuable solutions to the business
- improve DX
- level up platform quality
- improve development velocity
- be ready to scale, scale, scale

### Bounded Contexts

WIP, in the process of defining boundaries and BCs

#### Marketplace Domain

##### [Checkout](src/Marketplace/Checkout)

It is meant to be the piece of intelligence that handles Checkout Business process.

-----------------------------------

#### Finance Domain

##### [Invoicing](./src/Finance/Invoicing)

Disclaimer: no implementation so far, just a blueprint

-----------------------------------

### Architecture

The platform follows the Hexagonal Architecture and is structured using `modules`.

```text
.
├── app                                     <-- the platform runtime, where the framework of choice lives
├── etc                                     <-- mostly low level platform config
│   └── infrastructure                      <-- Infra configs for platform components
│       ├── mysql
│       ├── nginx
│       ├── php
│       ├── rabbitmq
│       └── traefik
├── src
│   ├── Common                              <-- Set of Common tools available for usage in other BC
│   │   ├── composer.json
│   │   ├── composer.lock
│   │   ├── docs
│   │   ├── src
│   │   └── tests
│   ├── Finance                             <-- Domain - Finance
│   │   └── Invoicing                       <-- A Bounded Context, a module in this architecture
│   │       ├── composer.json
│   │       ├── docs
│   │       ├── src
│   │       └── tests
│   └── Marketplace                         <-- Domain - Marketplace
│       └── Checkout                        <-- Another Bounded Context, another module in this architecture
│           ├── composer.json
│           ├── docs
│           ├── features
|           |   └── proposal_submission.feature
│           ├── src
│           │   ├── Application
│           │   ├── Domain
│           │   ├── Infrastructure
│           │   └── UI
│           └── tests
├── docker-compose.override.yml
├── docker-compose.override.yml.dist
├── docker-compose.yml
├── Dockerfile
├── Makefile
├── README.md

```

## More

### Debug

PHP Dockerfile comes with a build stage for debugging purposes.

To activate execute:

1. `cp docker-compose.yml.dist docker-compose.yml`
2. `make start`
3. Configure your IDE
4. DEBUG!
