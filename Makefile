cscheck:
	./vendor/bin/phpcs --standard=WordPress-Extra,WordPress-Docs  ./plugin/src/
csfix:
	./vendor/bin/phpcbf --standard=WordPress-Extra,WordPress-Docs  ./plugin/src/
release: csfix
	bin/create-release
