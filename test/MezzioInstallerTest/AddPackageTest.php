<?php

declare(strict_types=1);

namespace MezzioInstallerTest;

use Composer\Package\BasePackage;
use ReflectionProperty;

class AddPackageTest extends OptionalPackagesTestCase
{
    /**
     * @dataProvider packageProvider
     */
    public function testAddPackage(string $packageName, string $packageVersion, ?int $expectedStability): void
    {
        $installer = $this->createOptionalPackages();

        $this->io
            ->expects($this->atLeast(2))
            ->method('write')
            ->withConsecutive(
                [$this->stringContains('Removing installer development dependencies')],
                [$this->stringContains('Adding package')],
            );

        $installer->removeDevDependencies();
        $installer->addPackage($packageName, $packageVersion);

        self::assertPackage('laminas/laminas-stdlib', $installer);

        $r = new ReflectionProperty($installer, 'stabilityFlags');
        $r->setAccessible(true);
        $stabilityFlags = $r->getValue($installer);

        // Stability flags are only set for non-stable packages
        if ($expectedStability) {
            self::assertArrayHasKey($packageName, $stabilityFlags);
            self::assertEquals($expectedStability, $stabilityFlags[$packageName]);
        }
    }

    public function packageProvider(): array
    {
        // $packageName, $packageVersion, $expectedStability
        return [
            'dev'    => ['laminas/laminas-stdlib', '1.0.0-dev', BasePackage::STABILITY_DEV],
            'alpha'  => ['laminas/laminas-stdlib', '1.0.0-alpha2', BasePackage::STABILITY_ALPHA],
            'beta'   => ['laminas/laminas-stdlib', '1.0.0-beta.3', BasePackage::STABILITY_BETA],
            'RC'     => ['laminas/laminas-stdlib', '1.0.0-RC4', BasePackage::STABILITY_RC],
            'stable' => ['laminas/laminas-stdlib', '1.0.0', null],
        ];
    }
}
