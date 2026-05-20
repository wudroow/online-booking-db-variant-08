# Онлайн-запись в спортшколу (Вариант 8)

**Студент:** Выдрина Виктория  
**Группа:** 09.02.09  

База данных для онлайн-записи в спортшколу. Тренеры ведут возрастные группы.

## Уровень доступа к данным (DAL)

Реализован на PHP с использованием PDO. Архитектура: паттерн Repository.

### Структура классов

- Database – Singleton для подключения к БД
- RepositoryException – собственное исключение
- AbstractRepository – базовый репозиторий
- CoachRepository – работа с тренерами
- AthleteRepository – работа со спортсменами
- EnrollmentRepository – работа с записями (с транзакциями)

### Как запустить demo.php

1. Скопировать config.example.php в config.php и заполнить параметры БД
2. Загрузить файлы на хостинг Beget в папку public_html/sportschool
3. Открыть в браузере https://ваш_логин.beget.tech/sportschool/demo.php