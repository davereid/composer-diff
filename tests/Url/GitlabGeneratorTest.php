<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Tests\Url;

use IonBazan\ComposerDiff\Url\GitlabGenerator;
use IonBazan\ComposerDiff\Url\UrlGenerator;

class GitlabGeneratorTest extends GeneratorTest
{
    public function releaseUrlProvider(): array
    {
        return [
            'with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ],
            'without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ],
            'ssh with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package.git'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ],
            'ssh without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ],
            'dev version' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@gitlab.acme.org:ac/me/package'),
                null,
            ],
            'https in subgroup' => [
                $this->getPackageWithSource('ac/me/package', '3.12.1', 'https://gitlab.acme.org/ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package/tags/3.12.1',
            ],
            'ssh in subgroup' => [
                $this->getPackageWithSource('ac/me/package', '3.12.1', 'git@gitlab.acme.org:ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package/tags/3.12.1',
            ],
        ];
    }

    public function projectUrlProvider(): array
    {
        return [
            'with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package',
            ],
            'without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'),
                'https://gitlab.acme.org/acme/package',
            ],
            'ssh with .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package.git'),
                'https://gitlab.acme.org/acme/package',
            ],
            'ssh without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:acme/package'),
                'https://gitlab.acme.org/acme/package',
            ],
            'dev version' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'git@gitlab.acme.org:ac/me/package'),
                'https://gitlab.acme.org/ac/me/package',
            ],
            'https in subgroup' => [
                $this->getPackageWithSource('ac/me/package', '3.12.1', 'https://gitlab.acme.org/ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package',
            ],
            'ssh in subgroup' => [
                $this->getPackageWithSource('ac/me/package', '3.12.1', 'git@gitlab.acme.org:ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package',
            ],
        ];
    }

    public function compareUrlProvider(): array
    {
        return [
            'same maintainer' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...3.12.1',
            ],
            'without .git' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package'),
                'https://gitlab.acme.org/acme/package/compare/3.12.0...3.12.1',
            ],
            'dev versions' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', 'd46283075d76ed244f7825b378eeb1cee246af73'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', '9b860214d58c48b5cbe99bdb17914d0eb723c9cd'),
                'https://gitlab.acme.org/acme/package/compare/d462830...9b86021',
            ],
            'invalid or short reference' => [
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', 'd462830'),
                $this->getPackageWithSource('acme/package', 'dev-master', 'https://gitlab.acme.org/acme/package.git', '1'),
                'https://gitlab.acme.org/acme/package/compare/d462830...1',
            ],
            'compare with base fork' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/IonBazan/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/acme/package.git'),
                'https://gitlab.acme.org/acme/package/tags/3.12.1',
            ],
            'compare with head fork' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/IonBazan/package.git'),
                'https://gitlab.acme.org/IonBazan/package/tags/3.12.1',
            ],
            'compare with different repository provider' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/acme/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.org/acme/package.git'),
                null,
            ],
            'compare from https in subgroup' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/ac/me/package'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/ac/me/package'),
                'https://gitlab.acme.org/ac/me/package/compare/3.12.0...3.12.1',
            ],
            'compare from ssh in subgroup' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'git@gitlab.acme.org:ac/me/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'git@gitlab.acme.org:ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package/compare/3.12.0...3.12.1',
            ],
            'compare with base fork from subgroups' => [
                $this->getPackageWithSource('acme/package', '3.12.0', 'https://gitlab.acme.org/Ion/Bazan/package.git'),
                $this->getPackageWithSource('acme/package', '3.12.1', 'https://gitlab.acme.org/ac/me/package.git'),
                'https://gitlab.acme.org/ac/me/package/tags/3.12.1',
            ],
        ];
    }

    protected function getGenerator(): UrlGenerator
    {
        return new GitlabGenerator('gitlab.acme.org');
    }
}
