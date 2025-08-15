### KP-LITE API

## Бэкенд проекта KP-Lite (Symfony 7 + Microservices)

1. Команды для работы с сетью контейнеров:

```makefile
    make build #сборка
    make start #старт в фоне
    make stop #остановка
```

2. Скрипты для начала работы:

```makefile
    make init
```

3. Фикстуры:

```makefile
    make fixtures
```

4. Восстановление базы через dump-файл

```makefile
    make restore
```

5. Создание dump-файла

```makefile
    make dump
```

6. Тесты в контейнере (настроены в пайплайне)

```makefile
    make test
```

7. Очистка кэша

```makefile
    make clear-cache
```

7. Команда для индексирования Elastic Search

```makefile
    make es-index
```
