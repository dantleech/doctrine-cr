Doctrine Content Repository
===========================

[![Build Status](https://travis-ci.org/dantleech/doctrine-content-repository.svg?branch=master)](https://travis-ci.org/dantleech/doctrine-content-repository)
[![StyleCI](https://styleci.io/repos/<repo-id>/shield)](https://styleci.io/repos/<repo-id>)

What is this?
-------------

This allows you to organize Doctrine Entities in a tree hierarchy and identify
them with a UUID (universally unique identifier), for example:

```bash
/
    page-1/          [Acme\Entity\Page]
    page-2/          [Acme\Entity\Page]
        block-1/     [Acme\Entity\Block]
        block-2/     [Acme\Entity\Block]
        comment-1/   [Acme\Entity\Comment]
    foo/             [Acme\Entity\Foobar]
```

It is similar in concept to the
[PHPCR-ODM](https://github.com/doctrine/phpcr-odm) but uses the standard
Doctrine ORM.

You can access entities by path:

```php
$page1 = $entityManager->find(null, '/page-1');
$comment = $entityManager->find(null, '/page-1/comment1');
```

You can also access by UUID:

```php
$page1 = $entityManager->find(null, '6cb68641-f995-43d4-b698-5d61ae78fa90');
```

and traverse the tree:

```
$page1 = $entityManager->find(null, '/page-1');

foreach ($page1->getChildren() as $child) {
    $parent = $child->getParent();
}
```

Note that the above methods are not part of an interface, but are using mapped
properties - the DoctrineCR, like Doctrine itself, does not impose any
restrictions upon your domain model.

At a glance
-----------

- Hierarchical, tree, organization of Doctrine Entities;
- Global UUID (uuid4), class-less `->find`;
- Order preservation on path operations;

How does it work?
-----------------

Basically we store all the paths and UUIDs in a lookup table. Mapping is
applied to your domain models (Entities) indicating which fields should be
mapped with the Doctrine CR fields such as "children", "parent", "path",
"uuid", etc.  (UUID is mandatory).

DoctrineCR then includes an event subscriber which will listen to load and
persist events sent from the standard Doctrine `EntityManager` in addition to
decorating that same `EventManager` - adding the possiblity to lookup any
(managed) Entity by UUID and perform tree specific operations such as moving.

TODO:

[x] Children collection
[x] Map depth
[x] Children collection + depth + others on persist.
[x] Path\\Entry caching
[x] Implicit move
[ ] Explicit move
[ ] Remove
[ ] XSL Schema
[ ] Generalize lookUpBy / lookupFor / getByUuid / etc.
[ ] Rename CREvents to DcrEvents
[ ] Rename CRSubscriber to DcrSubscriber

[ ] Metadata cache
