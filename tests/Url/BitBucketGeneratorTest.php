<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\BitBucketGenerator;
use IonBazan\ComposerDiff\Url\UrlGenerator;

class BitBucketGeneratorTest extends GeneratorTest
{
    public function releaseUrlProvider(): array
    {
        return [
            'with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ],
            'without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ],
            'ssh with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package.git'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ],
            'ssh without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package'),
                'https://bitbucket.org/acme/package/src/3.12.1',
            ],
            'dev version' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@bitbucket.org:acme/package', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                'https://bitbucket.org/acme/package/src/d46283075d76ed244f7825b378eeb1cee246af73',
            ],
        ];
    }

    public function projectUrlProvider(): array
    {
        return [
            'with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package',
            ],
            'without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package',
            ],
            'ssh with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package.git'),
                'https://bitbucket.org/acme/package',
            ],
            'ssh without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@bitbucket.org:acme/package'),
                'https://bitbucket.org/acme/package',
            ],
            'dev version' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@bitbucket.org:acme/package', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                'https://bitbucket.org/acme/package',
            ],
        ];
    }

    public function compareUrlProvider(): array
    {
        return [
            'same maintainer' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/branches/compare/3.12.0%0D3.12.1',
            ],
            'without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package'),
                'https://bitbucket.org/acme/package/branches/compare/3.12.0%0D3.12.1',
            ],
            'dev versions' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://bitbucket.org/acme/package/branches/compare/d462830%0D9b86021',
            ],
            'invalid or short reference' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', 'd462830'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://bitbucket.org/acme/package.git', '1'),
                'https://bitbucket.org/acme/package/branches/compare/d462830%0D1',
            ],
            'compare with base fork' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/IonBazan/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/acme/package.git'),
                'https://bitbucket.org/acme/package/branches/compare/IonBazan/package:3.12.0%0Dacme/package:3.12.1',
            ],
            'compare with head fork' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://bitbucket.org/IonBazan/package.git'),
                'https://bitbucket.org/IonBazan/package/branches/compare/acme/package:3.12.0%0DIonBazan/package:3.12.1',
            ],
            'compare with different repository provider' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://bitbucket.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.org/acme/package.git'),
                null,
            ],
        ];
    }

    protected function getGenerator(): UrlGenerator
    {
        return new BitBucketGenerator();
    }
}
