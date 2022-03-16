<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\AliasPackage;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryInterface;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\PackageDiff;
use RuntimeException;

class PackageDiffTest extends TestCase
{
    /**
     * @param string[] $expected
     *
     * @dataProvider operationsProvider
     */
    public function testBasicUsage(array $expected, bool $dev, bool $withPlatform): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(
            __DIR__.'/fixtures/base/composer.lock',
            __DIR__.'/fixtures/target/composer.lock',
            $dev,
            $withPlatform
        );

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    public function testSameBaseAndTarget(): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getPackageDiff(
            __DIR__.'/fixtures/base/composer.lock',
            __DIR__.'/fixtures/base/composer.lock',
            true,
            true
        );

        $this->assertEmpty($operations);
    }

    /**
     * @param string[] $expected
     *
     * @dataProvider diffOperationsProvider
     */
    public function testDiff(array $expected, RepositoryInterface $oldRepository, RepositoryInterface $newRepository): void
    {
        $diff = new PackageDiff();
        $operations = $diff->getDiff($oldRepository, $newRepository);

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    /**
     * @param string[] $expected
     *
     * @dataProvider operationsProvider
     */
    public function testGitUsage(array $expected, bool $dev, bool $withPlatform): void
    {
        $diff = new PackageDiff();
        $this->prepareGit();
        $operations = $diff->getPackageDiff('HEAD', '', $dev, $withPlatform);

        $this->assertSame($expected, array_map([$this, 'entryToString'], $operations->getArrayCopy()));
    }

    public function testInvalidGitRef(): void
    {
        $diff = new PackageDiff();
        $this->prepareGit();
        $this->expectException(RuntimeException::class);
        $diff->getPackageDiff('invalid-ref', '', true, true);
    }

    public function diffOperationsProvider(): iterable
    {
        yield 'update alias version' => [
            [],
            new ArrayRepository([
                new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '1.0', '1.0'),
            ]),
            new ArrayRepository([
                new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '2.0', '2.0'),
            ]),
        ];
        yield 'same alias version but different actual package version' => [
            [
                'update vendor/package-a from 1.0 to 2.0',
            ],
            new ArrayRepository([
                new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '1.0', '1.0'),
            ]),
            new ArrayRepository([
                new AliasPackage(new Package('vendor/package-a', '2.0', '2.0'), '1.0', '1.0'),
            ]),
        ];
        yield 'uninstall aliased package' => [
            [
                'uninstall vendor/package-a 1.0',
            ],
            new ArrayRepository([
                new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '2.0', '2.0'),
            ]),
            new ArrayRepository([
            ]),
        ];
        yield 'add aliased package' => [
            [
                'install vendor/package-a 1.0',
            ],
            new ArrayRepository([]),
            new ArrayRepository([
                new AliasPackage(new Package('vendor/package-a', '1.0', '1.0'), '2.0', '2.0'),
            ]),
        ];
    }

    public function operationsProvider(): iterable
    {
        yield 'prod, with platform' => [
            [
                'install psr/event-dispatcher 1.0.0',
                'update roave/security-advisories from dev-master to dev-master',
                'install symfony/deprecation-contracts v2.1.2',
                'update symfony/event-dispatcher from v2.8.52 to v5.1.2',
                'install symfony/event-dispatcher-contracts v2.1.2',
                'install symfony/polyfill-php80 v1.17.1',
                'install php >=5.3',
            ],
            false,
            true,
        ];
        yield 'prod, no platform' => [
            [
                'install psr/event-dispatcher 1.0.0',
                'update roave/security-advisories from dev-master to dev-master',
                'install symfony/deprecation-contracts v2.1.2',
                'update symfony/event-dispatcher from v2.8.52 to v5.1.2',
                'install symfony/event-dispatcher-contracts v2.1.2',
                'install symfony/polyfill-php80 v1.17.1',
            ],
            false,
            false,
        ];
        yield 'dev, no platform' => [
            [
                'update phpunit/php-code-coverage from 8.0.2 to 7.0.10',
                'update phpunit/php-file-iterator from 3.0.2 to 2.0.2',
                'update phpunit/php-text-template from 2.0.1 to 1.2.1',
                'update phpunit/php-timer from 5.0.0 to 2.1.2',
                'update phpunit/php-token-stream from 4.0.2 to 3.1.1',
                'update phpunit/phpunit from 9.2.5 to 8.5.8',
                'update sebastian/code-unit-reverse-lookup from 2.0.1 to 1.0.1',
                'update sebastian/comparator from 4.0.2 to 3.0.2',
                'update sebastian/diff from 4.0.1 to 3.0.2',
                'update sebastian/environment from 5.1.1 to 4.2.3',
                'update sebastian/exporter from 4.0.1 to 3.1.2',
                'update sebastian/global-state from 4.0.0 to 3.0.0',
                'update sebastian/object-enumerator from 4.0.1 to 3.0.3',
                'update sebastian/object-reflector from 2.0.1 to 1.1.1',
                'update sebastian/recursion-context from 4.0.1 to 3.0.0',
                'update sebastian/resource-operations from 3.0.1 to 2.0.1',
                'update sebastian/type from 2.1.0 to 1.1.3',
                'update sebastian/version from 3.0.0 to 2.0.1',
                'uninstall phpunit/php-invoker 3.0.1',
                'uninstall sebastian/code-unit 1.0.3',
            ],
            true,
            false,
        ];
    }

    private function prepareGit(): void
    {
        $gitDir = __DIR__.'/test-git';
        @mkdir($gitDir);
        chdir($gitDir);
        exec('git init');
        exec('git config user.name test');
        exec('git config user.email test@example.com');
        file_put_contents($gitDir.'/composer.lock', file_get_contents(__DIR__.'/fixtures/base/composer.lock'));
        exec('git add composer.lock && git commit -m "init"');
        file_put_contents($gitDir.'/composer.lock', file_get_contents(__DIR__.'/fixtures/target/composer.lock'));
    }

    private function entryToString(DiffEntry $entry): string
    {
        $operation = $entry->getOperation();

        if ($operation instanceof InstallOperation) {
            return sprintf('install %s %s', $operation->getPackage()->getName(), $operation->getPackage()->getPrettyVersion());
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf('update %s from %s to %s', $operation->getInitialPackage()->getName(), $operation->getInitialPackage()->getPrettyVersion(), $operation->getTargetPackage()->getPrettyVersion());
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf('uninstall %s %s', $operation->getPackage()->getName(), $operation->getPackage()->getPrettyVersion());
        }

        throw new \InvalidArgumentException('Invalid operation provided');
    }
}
