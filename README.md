# Config

[![Action Status](https://github.com/amethyst-php/core/workflows/Test/badge.svg)](https://github.com/amethyst-php/core/actions)
[![Amethyst](https://img.shields.io/badge/Package-Amethyst-7e57c2)](https://github.com/amethyst-php/amethyst)

This is the core of all Amethyst packages.

# Requirements

- PHP from 8.1

## Installation

You can install it via [Composer](https://getcomposer.org/) by typing the following command:

```bash
composer require amethyst/core
```

The package will automatically register itself.

## Mapping the hell out of it

Amethyst takes a lot of advantages by mapping all models, relations and attributes; it does soo much that it's a requirement for every package:

- Each instance of Model must be converted in a readable string (e.g. classname, or morph name) and viceversa.
- Given an instance of Model it should be possible to retrieve all relations.
- Given an instance of Model it should be possibile to retrieve all attributes.

Note: It's important to notice that we are refering to an instance of Model instead of class of Model.

#### What are the benefits then?

Because having this kind of information will make a lot of things easy (for e.g. auto joins, creating views)

#### How it's done and how extend it
All amethyst packages are automatically mapped, that's because the mapping use the configuration under `amethyst` to retrieve all models 

This is done by using the package [eloquent-mapper](https://github.com/railken/eloquent-mapper).

## How interact with the data

There are two ways to interact with the data: Through code or with http calls

### Code



### Http calls

Each data has the following operation: 
    - 'Create a new record'
    - 'Retrieve a single record'
    - 'Update a single ecord'
    - 'Delete a single record'
    - 'Show multiple records'
    - 'Remove multiple records'
    - 'Update multiple records'

## How customize the data

One of the key package used to handle data is [lem](https://github.com/railken/lem). This package provide a way to define a schema, validate, authorize, serialize and handle errors; all of this incapsulated by a class, called the `Manager`.

https://github.com/amethyst-php/cli


## Testing

- Clone this repository
- Copy the default `phpunit.xml.dist` to `phpunit.xml`
- Change the environment variables as you see fit
- Launch `./vendor/bin/phpunit`
