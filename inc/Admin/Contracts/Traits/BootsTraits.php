<?php

namespace HBP\Disabler\Admin\Contracts\Traits;

use function Hybrid\Tools\class_basename;
use function Hybrid\Tools\class_uses_recursive;

trait BootsTraits {

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    protected static $traitInitializers = [];

    /**
     * Boot all of the bootable traits on the class.
     *
     * @return void
     */
    protected static function bootTraits() {
        $class = static::class;

        $booted = [];

        static::$traitInitializers[ $class ] = [];

        foreach ( class_uses_recursive( $class ) as $trait ) {
            $method = 'boot' . class_basename( $trait );

            if ( method_exists( $class, $method ) && ! in_array( $method, $booted ) ) {
                forward_static_call( [ $class, $method ] );

                $booted[] = $method;
            }

            if ( method_exists( $class, $method = 'initialize' . class_basename( $trait ) ) ) {
                static::$traitInitializers[ $class ][] = $method;

                static::$traitInitializers[ $class ] = array_unique( static::$traitInitializers[ $class ] );
            }
        }
    }

}
