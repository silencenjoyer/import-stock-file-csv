# Short description

This package provides the ability to import a csv file with product data. But it includes the possibility to add import of other files.

## Installation
### Clone repository:
```shell
 git clone git@github.com:silencenjoyer/import-stock-file-csv.git
```
### Move to the directory with the project and execute:
```shell
 docker-compose -f docker/docker-compose.yml --env-file .env.dev up -d
```
### Run migrations:
```shell
 docker-compose -f docker/docker-compose.yml run --rm php php bin/console doctrine:migration:migrate
```

## Usage

Commands can be executed from outside the container. In this case, the syntax will be as when we performed the migration:
```shell
 docker-compose -f docker/docker-compose.yml run --rm php {command}
```
It is also possible to log into the container and execute commands as on the host where php is deployed.
```shell
 docker exec -it docker-php-1 bash
```
In this case, the *docker-php-1* container name is the default. It may be different on your system, then find the correct container using **docker ps -a**.  

Next, I'll be giving an example of commands that should be in the {command} placeholder if you're outside the container. If you are inside, then you can just execute the commands.  

Run file import from a task: 
```shell
 php bin/console app:import-stock
```
By default, the file is taken from the /tests/files/stock.csv. But you can specify a custom path to the file by passing the -f {path} flag. The path must be accessible from within the php container.  
```shell
 php bin/console app:import-stock -f /some/path/test.csv
```
or
```shell
 php bin/console app:import-stock --file=/some/path/test.csv
```
Also, the command can be executed in test mode. In this case, the same logic will be done, but the data will not be stored in the database table. This is done by passing the -t flag (--test)  
```shell
 php bin/console app:import-stock -t
```
or
```shell
 php bin/console app:import-stock --test
```

### Testing
```shell
 php bin/phpunit
```
or
```shell
 docker-compose -f docker/docker-compose.yml run --rm php php bin/phpunit
```