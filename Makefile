NAME=payum-server
REPO=payum
REPONAME=$(REPO)/$(NAME)

## help
all:
	echo "build|up|down|start|stop|reload|enter|status|drop|develop|commit|pull"

## build vm
build:
	docker build --rm --tag $(REPONAME) .

## start vm
up:
	docker run --rm --interactive --tty -p 80:80 --name $(NAME) $(REPONAME)

updev:
	docker run --rm --interactive --tty -p 80:80 --volume `pwd`:/app --name $(NAME) $(REPONAME) /bin/bash

## stop vm
down:
	docker rm --force $(NAME)

## start vm
start: up

## stop vm
stop: down

drop:
	docker rmi $(REPONAME)

## reload vm
reload: down up

## enter inside to vm (debug only)
enter:
	docker run --rm -it --name $(NAME) $(REPONAME) /bin/bash

status:
	docker attach $(NAME)

push:
	docker push $(REPONAME)

commit:
	docker commit $(NAME) $(REPONAME)

pull:
	docker pull $(REPONAME)