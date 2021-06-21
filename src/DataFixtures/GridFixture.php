<?php

namespace App\DataFixtures;

use App\Entity\Layout\Grid;
use App\Entity\Layout\GridCol;
use App\Entity\Security\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * GridFixture
 *
 * Grid Fixture management
 *
 * @property User $webmaster
 * @property int $position
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class GridFixture extends BaseFixture implements DependentFixtureInterface
{
    /** @var User $webmaster */
    private $webmaster;
    private $position = 1;

    /**
     * {@inheritDoc}
     */
    protected function loadData(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->webmaster = $this->getReference('webmaster');

        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 12', [12]);
        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 6-6', [6, 6]);
        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 4-4-4', [4, 4, 4]);
        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 3-3-3-3', [3, 3, 3, 3]);
        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 4-8', [4, 8]);
        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 8-4', [8, 4]);
        $this->generateGrid($this->translator->trans('Grille', [], 'admin') . ' 3-6-3', [3, 6, 3]);

        $this->manager->flush();
    }

    /**
     * Generate Grid
     *
     * @param string $adminName
     * @param array $cols
     */
    private function generateGrid(string $adminName, array $cols)
    {
        $grid = new Grid();
        $grid->setAdminName($adminName);
        $grid->setCreatedBy($this->webmaster);
        $grid->setPosition($this->position);

        foreach ($cols as $key => $col) {

            $newCol = new GridCol();
            $newCol->setSize($col);
            $newCol->setGrid($grid);
            $newCol->setPosition($key + 1);
            $newCol->setCreatedBy($this->webmaster);

            $this->manager->persist($newCol);
        }

        $this->position++;
        $this->manager->persist($grid);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            SecurityFixture::class
        ];
    }
}