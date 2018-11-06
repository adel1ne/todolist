# Framework Name • [TodoMVC](http://todomvc.com)

> Official description of the framework (from its website)

## Getting started

- Clone this repo and install the dependencies with: 
- [npm](https://npmjs.com) by running: `npm install`
- [composer](https://getcomposer.org/) by running: `composer install`

- Import **db.sql** to get DB schema and tables
[database](mysql) 

## Implementation

- PHP Backend - is my own, specially created for this task.

- If you don't understand how the interface work, it ok!
Everyone to whom i showed it (including my wife and my dog), was in the same confusion.

**Ладно, теперь по-русски:** 

- Из допов успел реализовать ни-че-го :) 

- Немного перемудрил сам себя, выдумывая интерфейс и логику.
В итоге, в какой-то момент все усложнилось, а точка невозврата по времени была пройдена.
Поэтому не успел прикрутить расшаривание списков и уровень доступа (а очень хотелось)

- Кэширование тоже не успел, но идея была класть в кэш все изменения по спискам,
через **Redis** или **Memcached** по ключам: **list_name#user_id**. Дальше при выборе списка можно
было бы обращатся к базе, только если соответствующего ключа нет в кэше.

## Credit

Created by [Sergey Lymarev](https://vk.com/lymarev_s)
