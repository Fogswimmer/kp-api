### Инициализация проекта на UNIX
1. Сборка сети контейнеров:
```bash
    docker compose up --build -d
```
2. Запуск make: 
```bash
    make init
```
3. Опционально - для наполнения базы данных:

```bash
   docker exec -i db psql -U symfony -d symfony < dump.sql
```

### Cпособы инициализации на Windows
1. Используйте команду make init-all в среде WSL
2. Используйте Git Bash
3. Установите make c помощью choco:
```powershell
choco install make
```
