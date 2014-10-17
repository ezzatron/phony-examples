<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Mock\Builder;

use Eloquent\Phony\Mock\Builder\Definition\Method\CustomMethodDefinition;
use Eloquent\Phony\Mock\Builder\Definition\Method\MethodDefinitionCollection;
use Eloquent\Phony\Mock\Builder\Definition\Method\RealMethodDefinition;
use Eloquent\Phony\Mock\Builder\Exception\FinalClassException;
use Eloquent\Phony\Mock\Builder\Exception\FinalizedMockException;
use Eloquent\Phony\Mock\Builder\Exception\InvalidClassNameException;
use Eloquent\Phony\Mock\Builder\Exception\InvalidTypeException;
use Eloquent\Phony\Mock\Builder\Exception\MultipleInheritanceException;
use Eloquent\Phony\Mock\Factory\MockFactory;
use Eloquent\Phony\Mock\Factory\MockFactoryInterface;
use Eloquent\Phony\Mock\MockInterface;
use Eloquent\Phony\Stub\StubVerifierInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Builds mock classes.
 *
 * @internal
 */
class MockBuilder implements MockBuilderInterface
{
    /**
     * The regular expression used to validate symbol names.
     */
    const SYMBOL_PATTERN = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*';

    /**
     * Construct a new mock builder.
     *
     * @param array<string|object>|string|object|null $types      Types to add.
     * @param array|object|null                       $definition The definition.
     * @param string|null                             $className  The class name.
     * @param integer|null                            $id         The identifier.
     * @param MockFactoryInterface|null               $factory    The factory.
     *
     * @throws MockBuilderExceptionInterface If invalid input is supplied.
     */
    public function __construct(
        $types = null,
        $definition = null,
        $className = null,
        $id = null,
        MockFactoryInterface $factory = null
    ) {
        if (null === $factory) {
            $factory = MockFactory::instance();
        }

        $this->factory = $factory;

        $this->types = array();
        $this->reflectors = array();
        $this->methods = array();
        $this->staticMethods = array();
        $this->properties = array();
        $this->staticProperties = array();
        $this->constants = array();
        $this->id = $id;
        $this->isFinalized = false;

        $reflectorReflector = new ReflectionClass('ReflectionClass');
        $this->isTraitSupported = $reflectorReflector->hasMethod('isTrait');

        if (null === $types) {
            $this->normalize();
        } else {
            $this->like($types);
        }

        if (null !== $definition) {
            $this->define($definition);
        }

        $this->named($className);
    }

    /**
     * Get the factory.
     *
     * @return MockFactoryInterface The factory.
     */
    public function factory()
    {
        return $this->factory;
    }

    /**
     * Add classes, interfaces, or traits.
     *
     * @param string|object|array<string|object> $type      A type, or types to add.
     * @param string|object|array<string|object> $types,... Additional types to add.
     *
     * @return MockBuilderInterface          This builder.
     * @throws MockBuilderExceptionInterface If invalid input is supplied, or this builder is already finalized.
     */
    public function like($type)
    {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        $types = array();

        foreach (func_get_args() as $type) {
            if (is_array($type)) {
                $types = array_merge($types, $type);
            } else {
                $types[] = $type;
            }
        }

        $toAdd = array();

        foreach ($types as $type) {
            if (is_object($type)) {
                if ($type instanceof MockBuilderInterface) {
                    $toAdd = array_merge($toAdd, $type->types());
                } else {
                    $toAdd[] = get_class($type);
                }
            } elseif (is_string($type)) {
                $toAdd[] = $type;
            } else {
                throw new InvalidTypeException($type);
            }
        }

        $this->normalize($toAdd);

        return $this;
    }

    /**
     * Add custom methods and properties via a definition.
     *
     * @param array|object $definition The definition.
     *
     * @return MockBuilderInterface This builder.
     */
    public function define($definition)
    {
        if (is_object($definition)) {
            $definition = get_object_vars($definition);
        }

        foreach ($definition as $name => $value) {
            $nameParts = explode(' ', $name);
            $name = array_pop($nameParts);
            $isStatic = in_array('static', $nameParts);
            $isFunction = in_array('function', $nameParts);
            $isProperty = in_array('var', $nameParts);
            $isConstant = in_array('const', $nameParts);

            if (!$isFunction && !$isProperty && !$isConstant) {
                if (is_object($value) && is_callable($value)) {
                    $isFunction = true;
                } else {
                    $isProperty = true;
                }
            }

            if ($isFunction) {
                if ($isStatic) {
                    $this->addStaticMethod($name, $value);
                } else {
                    $this->addMethod($name, $value);
                }
            } elseif ($isConstant) {
                $this->addConstant($name, $value);
            } else {
                if ($isStatic) {
                    $this->addStaticProperty($name, $value);
                } else {
                    $this->addProperty($name, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Add a custom method.
     *
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addMethod($name, $callback = null)
    {
        $this->methods[$name] = $callback;

        return $this;
    }

    /**
     * Add a custom static method.
     *
     * @param string        $name     The name.
     * @param callable|null $callback The callback.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addStaticMethod($name, $callback = null)
    {
        $this->staticMethods[$name] = $callback;

        return $this;
    }

    /**
     * Add a custom property.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addProperty($name, $value = null)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * Add a custom static property.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return MockBuilderInterface This builder.
     */
    public function addStaticProperty($name, $value = null)
    {
        $this->staticProperties[$name] = $value;

        return $this;
    }

    /**
     * Add a custom class constant.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     */
    public function addConstant($name, $value)
    {
        $this->constants[$name] = $value;

        return $this;
    }

    /**
     * Set the class name.
     *
     * @param string $className|null The class name, or null to use a generated name.
     *
     * @return MockBuilderInterface          This builder.
     * @throws MockBuilderExceptionInterface If this builder is already finalized.
     */
    public function named($className = null)
    {
        if ($this->isFinalized) {
            throw new FinalizedMockException();
        }

        if (null !== $className) {
            if (
                !preg_match('/^' . static::SYMBOL_PATTERN . '$/S', $className)
            ) {
                throw new InvalidClassNameException($className);
            }
        }

        $this->className = $className;

        return $this;
    }

    /**
     * Get a mock.
     *
     * This method will return the current mock, only creating a new mock if no
     * existing mock is available.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MockInterface The mock instance.
     */
    public function get()
    {
        if ($this->mock) {
            return $this->mock;
        }

        return $this->create();
    }

    /**
     * Create a new mock.
     *
     * This method will always create a new mock, and will replace the current
     * mock.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return MockInterface The mock instance.
     */
    public function create()
    {
        $this->mock = $this->factory->createMock($this);
    }

    /**
     * Generate and define the mock class.
     *
     * Calling this method will finalize the mock builder.
     *
     * @return ReflectionClass The class.
     */
    public function build()
    {
        return $this->factory->createMockClass($this);
    }

    /**
     * Finalize the mock builder.
     *
     * @return MockBuilderInterface This builder.
     */
    public function finalize()
    {
        $this->isFinalized = true;
        $this->methodDefinitions = $this->buildMethodDefinitions();

        return $this;
    }

    /**
     * Get the identifier.
     *
     * @return integer|null The identifier.
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Get the class name.
     *
     * @return string The class name.
     */
    public function className()
    {
        if (null !== $this->className) {
            return $this->className;
        }

        return $this->generatedClassName;
    }

    /**
     * Get the parent class name.
     *
     * @return string|null The parent class name, or null if the mock will not extend a class.
     */
    public function parentClassName()
    {
        return $this->parentClassName;
    }

    /**
     * Get the interface names.
     *
     * @return array<string> The interface names.
     */
    public function interfaceNames()
    {
        return $this->interfaceNames;
    }

    /**
     * Get the trait names.
     *
     * @return array<string> The trait names.
     */
    public function traitNames()
    {
        return $this->traitNames;
    }

    /**
     * Get the types.
     *
     * @return array<string> The types.
     */
    public function types()
    {
        return $this->types;
    }

    /**
     * Get the type reflectors.
     *
     * @return array<string,ReflectionClass> The type reflectors.
     */
    public function reflectors()
    {
        return $this->reflectors;
    }

    /**
     * Get the custom methods.
     *
     * @return array<string,callable|null> The custom methods.
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Get the method definitions.
     *
     * @return MethodDefinitionCollectionInterface|null The definitions, or null if the builder is not yet finalized.
     */
    public function methodDefinitions()
    {
        return $this->methodDefinitions;
    }

    /**
     * Get the custom static methods.
     *
     * @return array<string,callable|null> The custom static methods.
     */
    public function staticMethods()
    {
        return $this->staticMethods;
    }

    /**
     * Get the custom properties.
     *
     * @return array<string,mixed> The custom properties.
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Get the custom static properties.
     *
     * @return array<string,mixed> The custom static properties.
     */
    public function staticProperties()
    {
        return $this->staticProperties;
    }

    /**
     * Get the custom constants.
     *
     * @return array<string,mixed> The custom constants.
     */
    public function constants()
    {
        return $this->constants;
    }

    /**
     * Returns true if this builder is finalized.
     *
     * @return boolean True if finalized.
     */
    public function isFinalized()
    {
        return $this->isFinalized;
    }

    /**
     * Get a static stub.
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string $name The method name.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the method does not exist.
     */
    public function staticStub($name)
    {
        $class = $this->build();

        $property = $class->getProperty('_staticStubs');
        $property->setAccessible(true);
        $stubs = $property->getValue($mock);

        if (isset($stubs[$name])) {
            return $stubs[$name];
        }

        throw new BadMethodCallException(
            sprintf(
                'No stub defined for method %s::%s()',
                get_class($mock),
                $name
            )
        );
    }

    /**
     * Get a stub.
     *
     * Calling this method will finalize the mock builder, unless a mock is
     * supplied.
     *
     * @param string             $name The method name.
     * @param MockInterface|null $mock The mock, or null to use the current mock.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the method does not exist.
     */
    public function stub($name, MockInterface $mock = null)
    {
        if (!$mock) {
            $mock = $this->get();
        }

        $property = new ReflectionProperty($mock, '_stubs');
        $property->setAccessible(true);
        $stubs = $property->getValue($mock);

        if (isset($stubs[$name])) {
            return $stubs[$name];
        }

        throw new BadMethodCallException(
            sprintf(
                'No stub defined for method %s::%s()',
                get_class($mock),
                $name
            )
        );
    }

    /**
     * Get a stub, and modify its current criteria to match the supplied
     * arguments (and possibly others).
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string $name         The method name.
     * @param mixed  $argument,... The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the method does not exist.
     */
    public function stubWith($name)
    {
        $arguments = func_get_args();
        array_shift($arguments);

        return call_user_func_array(
            array($this->stub($name), 'with'),
            $arguments
        );
    }

    /**
     * Get a stub, and modify its current criteria to match the supplied
     * arguments (and no others).
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string $name         The method name.
     * @param mixed  $argument,... The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the method does not exist.
     */
    public function stubWithExactly($name)
    {
        $arguments = func_get_args();
        array_shift($arguments);

        return call_user_func_array(
            array($this->stub($name), 'withExactly'),
            $arguments
        );
    }

    /**
     * Get a stub, and modify its current criteria to match the supplied
     * arguments (and possibly others).
     *
     * Calling this method will finalize the mock builder.
     *
     * @param string               $name      The method name.
     * @param array<integer,mixed> $arguments The arguments.
     *
     * @return StubVerifierInterface  The stub verifier.
     * @throws BadMethodCallException If the method does not exist.
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array(
            array($this->stub($name), 'with'),
            $arguments
        );
    }

    /**
     * Normalize the specified build parameters.
     *
     * @throws MockBuilderExceptionInterface If invalid input is supplied.
     */
    protected function normalize(array $toAdd = null)
    {
        if (null === $toAdd) {
            $toAdd = array();
        } else {
            $toAdd = array_unique($toAdd);

            foreach ($toAdd as $index => $type) {
                if (in_array($type, $this->types, true)) {
                    unset($toAdd[$index]);
                }
            }
        }

        $reflectors = $this->reflectors;

        foreach ($toAdd as $type) {
            try {
                $reflectors[$type] = $reflector = new ReflectionClass($type);
            } catch (ReflectionException $e) {
                throw new InvalidTypeException($type, $e);
            }

            if ($reflector->isFinal()) {
                throw new FinalClassException($type);
            }
        }

        $parentClassCount = 0;
        $parentClassNames = array();
        $parentClassName = null;
        $interfaceNames = array();
        $traitNames = array();

        foreach ($reflectors as $reflector) {
            $className = $reflector->getName();

            if ($reflector->isInterface()) {
                $interfaceNames[] = $className;
            } elseif ($this->isTraitSupported && $reflector->isTrait()) {
                $traitNames[] = $className;
            } else {
                $parentClassNames[] = $className;
                $parentClassCount++;

                if (null === $parentClassName) {
                    $parentClassName = $className;
                }
            }
        }

        if ($parentClassCount > 1) {
            throw new MultipleInheritanceException($parentClassNames);
        }

        $this->types = array_merge($this->types, $toAdd);
        $this->reflectors = $reflectors;
        $this->parentClassName = $parentClassName;
        $this->interfaceNames = $interfaceNames;
        $this->traitNames = $traitNames;

        $this->generatedClassName = $this->generateClassName(
            $parentClassName,
            $interfaceNames,
            $traitNames,
            $this->id
        );
    }

    /**
     * Build the method definitions.
     *
     * @return MethodDefinitionCollectionInterface The method definitions.
     */
    protected function buildMethodDefinitions()
    {
        $methods = array();
        $parameterCounts = array();

        foreach ($this->reflectors as $type) {
            foreach ($type->getMethods() as $method) {
                $name = $method->getName();

                if (
                    !$method->isFinal() &&
                    !$method->isPrivate() &&
                    !$method->isConstructor()
                ) {
                    $parameterCount = $method->getNumberOfParameters();

                    if (
                        !isset($methods[$name]) ||
                        $parameterCount > $parameterCounts[$name]
                    ) {
                        $methods[$name] = new RealMethodDefinition($method);
                        $parameterCounts[$name] = $parameterCount;
                    }
                }
            }
        }

        foreach ($this->staticMethods as $name => $callback) {
            $methods[$name] =
                new CustomMethodDefinition(true, $name, $callback);
        }

        foreach ($this->methods as $name => $callback) {
            $methods[$name] =
                new CustomMethodDefinition(false, $name, $callback);
        }

        ksort($methods, SORT_STRING);

        return new MethodDefinitionCollection($methods);
    }

    /**
     * Generate a mock class name.
     *
     * @param string|null                $parentClassName The parent class name.
     * @param array<integer,string>|null $interfaceNames  The interface names.
     * @param array<integer,string>|null $traitNames      The trait names.
     * @param integer|null               $id              The identifier.
     *
     * @return string The generated class name.
     */
    protected function generateClassName(
        $parentClassName = null,
        array $interfaceNames = null,
        array $traitNames = null,
        $id = null
    ) {
        $className = 'PhonyMock';

        if (null !== $parentClassName) {
            $subject = $parentClassName;
        } elseif ($interfaceNames) {
            $subject = $interfaceNames[0];
        } elseif ($traitNames) {
            $subject = $traitNames[0];
        } else {
            $subject = null;
        }

        if ($subject) {
            $subjectAtoms = preg_split('/[_\\\\]/', $subject);
            $className .= '_' . array_pop($subjectAtoms);
        }

        if (null === $id) {
            $className .= '_' . substr(md5(uniqid()), 0, 6);
        } else {
            $className .= '_' . $id;
        }

        return $className;
    }

    protected $isTraitSupported;
    private $factory;
    private $types;
    private $reflectors;
    private $methods;
    private $staticMethods;
    private $properties;
    private $staticProperties;
    private $constants;
    private $className;
    private $generatedClassName;
    private $parentClassName;
    private $interfaceNames;
    private $traitNames;
    private $isFinalized;
    private $methodDefinitions;
    private $mock;
}
