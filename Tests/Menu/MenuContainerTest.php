<?php
namespace Rzeka\MenuBundle\Tests\Menu;

use PHPUnit\Framework\TestCase;
use Rzeka\Menu\MenuItem;
use Rzeka\MenuBundle\Exception\InvalidMenuNameException;
use Rzeka\MenuBundle\Exception\MenuBuilderArgumentsUnsupportedException;
use Rzeka\MenuBundle\Menu\MenuBuilderArgsInterface;
use Rzeka\MenuBundle\Menu\MenuBuilderInterface;
use Rzeka\MenuBundle\Menu\MenuContainer;

class MenuContainerTest extends TestCase
{
    public function testAddBuilder()
    {
        $builder = $this->createMock(MenuBuilderInterface::class);
        $builder
            ->expects(static::once())
            ->method('getName');

        $container = new MenuContainer();
        $container->addBuilder($builder);
    }

    public function testGetNames()
    {
        $names = [
            'test',
            'example'
        ];

        $builders = [];
        foreach ($names as $name) {
            $builder = $this->createMock(MenuBuilderInterface::class);
            $builder
                ->expects(static::once())
                ->method('getName')
                ->willReturn($name);

            $builders[] = $builder;
        }

        $container = new MenuContainer();

        foreach ($builders as $builder) {
            $container->addBuilder($builder);
        }

        static::assertEquals($names, $container->getNames());
    }

    public function testGetMenu()
    {
        $name = 'test';
        $menu = new MenuItem($name);

        $builder = $this->createMock(MenuBuilderInterface::class);
        $builder
            ->expects(static::once())
            ->method('getName')
            ->willReturn($name);

        $builder
            ->expects(static::once())
            ->method('build')
            ->willReturn($menu);

        $container = new MenuContainer();
        $container->addBuilder($builder);

        $result = $container->getMenu($name);
        static::assertEquals($menu, $result);

        //shouldn't call builder
        $container->getMenu($name);
    }

    public function testGetNonExistentMenu()
    {
        $container = new MenuContainer();

        $this->expectException(InvalidMenuNameException::class);
        $container->getMenu('test');
    }

    public function testGetMenuWithArgs()
    {
        $uniqueId = 'test';
        $args = [
            'testArg'
        ];
        $name = 'test';
        $menu = new MenuItem($name);

        $builder = $this->createMock(MenuBuilderArgsInterface::class);
        $builder
            ->expects(static::once())
            ->method('getName')
            ->willReturn($name);

        $builder
            ->expects(static::once())
            ->method('buildWithArgs')
            ->with(...$args)
            ->willReturn($menu);

        $container = new MenuContainer();
        $container->addBuilder($builder);

        $result = $container->getMenuWithArgs($name, $uniqueId, ...$args);
        static::assertEquals($menu, $result);

        //shouldn't call builder
        $container->getMenuWithArgs($name, $uniqueId, ...$args);
    }

    public function testGetNonExistentMenuWithArgs()
    {
        $container = new MenuContainer();

        $this->expectException(InvalidMenuNameException::class);
        $container->getMenuWithArgs('test', '', ...['test']);
    }

    public function testGetMenuWithArgsNoArgsInterface()
    {
        $name = 'test';

        $builder = $this->createMock(MenuBuilderInterface::class);
        $builder
            ->expects(static::once())
            ->method('getName')
            ->willReturn($name);

        $container = new MenuContainer();
        $container->addBuilder($builder);

        $this->expectException(MenuBuilderArgumentsUnsupportedException::class);
        $container->getMenuWithArgs($name, 'test', ...['test']);
    }
}
