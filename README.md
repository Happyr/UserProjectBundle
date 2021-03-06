# Happyr User Project Bundle

If you have multiple users that will share access to one or more objects.

Installation
------------

### Step 1: Using Composer

Install it with Composer!

```js
// composer.json
{
    // ...
    require: {
        // ...
        "happyr/user-project-bundle": "dev-master",
    }
}
```

Then, you can install the new dependencies by running Composer's ``update``
command from the directory where your ``composer.json`` file is located:

```bash
$ php composer.phar update
```

### Step 2: Register the bundle

 To register the bundles with your kernel:

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Happyr\UserProjectBundle\HappyrUserProjectBundle(),
    // ...
);
```
### Step 3: Init ACL

If you have not done it before, it is time to init the ACL. If you like, you could read about
[access control lists](http://symfony.com/doc/current/cookbook/security/acl.html) or
you could just run this command:

```bash
php app/console init:acl
```

### Step 4: Configure the bundle

``` yaml
# app/config/config.yml

#happyr_user_project:
  #no config at this point
```

Requirements
------------

Your User object must implement Happyr\UserProjectBundle\Model\ProjectMemberInterface.
Your other object that is in the project must implement Happyr\UserProjectBundle\Model\ProjectObjectInterface.

http://symfony.com/doc/current/cookbook/doctrine/resolve_target_entity.html

``` php
use Happyr\UserProjectBundle\Model\ProjectObjectInterface;
use Happyr\UserProjectBundle\Entity\Project;

class MyObject implements ProjectObjectInterface
{
    /**
     * @var \Happyr\UserProjectBundle\Entity\Project project
     *
     * @ORM\ManyToOne(targetEntity="Happyr\UserProjectBundle\Entity\Project", inversedBy="objects", cascade={"persist"})
     *
     */
    protected $project;

    public function getId()
    {
        return $this->id
    }

    public function getProject()
    {
        $this->project;
    }

    /**
     *
     * @param Project $project
     *
     */
    public function setProject(Project $project)
    {
        $this->project=$project;
    }
}
```

### Routing

```yml
# app/config/routing.yml
happyr_user_project:
    resource: "@HappyrUserProjectBundle/Resources/config/routing.yml"
    prefix:   /
```
