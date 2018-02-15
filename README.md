# docker-ldap-php

# Part One : Install docker

You'll need both [docker-machine](https://docs.docker.com/machine/install-machine/#install-machine-directly) and [docker-compose](https://docs.docker.com/compose/install/) on your machine.

Before running the docker, please ensure that any service are running on both port 80 and 389 of your host machine.

# Part Two : How to use the stack

## 1. Build the docker images
```
$ docker-compose build
```

## 2. Launch the stack
```bash
$ docker-compose up
# daemon mode
$ docker-compose up -d
```

## 3. Use the stack

### List dockers 

At this point you can check the running container with
```
$ docker ps -a
```

### Reach dockers 

You can get the phpLDAPadmin IP by running :  
```bash
$ docker inspect -f "{{.NetworkSettings.Networks.dockerldapphp_default.IPAddress}}" ldapphp_phpldapadmin_1
```

You can then access the phpLDAPadmin at https://$IP_LDAP and log with those credentials :

| Login DN                   | Password |
|:--------------------------:|:--------:|
| cn=admin,dc=example,dc=org | admin    |

### Frafos Entries

You can load the frafos ldap entries this way : 

```sh
11:00:17 ❯ docker exec -it dockerldapphp_ldap_1 ldapadd -x -D "cn=admin,dc=example,dc=org" -w admin -H ldap:// -f /ldap_entries/add_content.ldif
```

### Update password

You can then update the password for a specific user from the phpldapadmin interface !

### php code

All php code present in the ./code directory can be reached on `localhost` or `127.0.0.1`

### php code specific 

#### index.html

`LDAPHelper.php` is a simple class that wrap the interaction with the LDAP.

`HandleForm.php` is a simple php code that try to authentificate a user with the data from a request trough the `LDAPHelper` class.

`index.html` is a simple html form that redirect to the `HandleForm.php` file. Reach it at `127.0.0.1/index.html`. 

You can then authenticate into the ldap with those value : 

| host | ldap_dn | group | username | password |
|:----:|:-------:|:-----:|:--------:|:--------:|
| your ldap host | 	dc=example,dc=org |  | cn=admin | admin |
| your ldap host | 	dc=example,dc=org | ou=People | uid=john | password set in previous step  |

## 4. Stop the stack
You can stop the stack by running : 
```bash
$ docker-compose stop
```

## 5. Remove the stack
You can remove the stack by running : 
```bash
docker-compose down
```

# TODO

- [x] how to
- [x] basic login ok 
