<?php

namespace App\Controller\Admin\Translation;

use App\Controller\Admin\AdminController;
use App\Entity\Translation\TranslationDomain;
use App\Entity\Translation\TranslationUnit;
use App\Form\Type\Translation\DomainType;
use App\Helper\Admin\IndexHelper;
use App\Repository\Translation\TranslationDomainRepository;
use App\Repository\Translation\TranslationUnitRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * DomainController
 *
 * Translation Domain management
 *
 * @Route("/admin-%security_token%/{website}/translations/domains", schemes={"%protocol%"})
 * @IsGranted("ROLE_TRANSLATION")
 *
 * @property TranslationDomain $class
 * @property DomainType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class DomainController extends AdminController
{
    protected $class = TranslationDomain::class;
    protected $formType = DomainType::class;

    /**
     * Index TranslationDomain
     *
     * @Route("/index", methods={"GET"}, name="admin_translationdomain_index", options={"expose"=true})
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        $this->template = 'admin/page/translation/domains.html.twig';
        $this->arguments['domains'] = $this->entities = $this->entityManager->getRepository(TranslationDomain::class)->findAllDomains();
        $this->forceEntities = true;

        if (!$this->authorizationChecker->isGranted('ROLE_INTERNAL')) {

            $website = $this->getWebsite($request);
            $websiteDomains = $website->getConfiguration()->getTransDomains();

            $domainsIds = [];
            foreach ($websiteDomains as $domain) {
                $domainsIds[] = $domain->getId();
            }

            foreach ($this->entities as $key => $entity) {
                if (!in_array($entity->getId(), $domainsIds)) {
                    unset($this->entities[$key]);
                }
            }
        }

        return parent::index($request);
    }

    /**
     * Edit TranslationDomain
     *
     * @Route("/edit/{translationdomain}", methods={"GET", "POST"}, name="admin_translationdomain_edit")
     * @IsGranted("ROLE_INTERNAL")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Edit Domain
     *
     * @Route("/edit/domain/{translationdomain}", methods={"GET"}, name="admin_translationsdomain_edit")
     *
     * @param Request $request
     * @param TranslationDomainRepository $domainRepository
     * @param TranslationUnitRepository $unitRepository
     * @return Response
     * @throws NonUniqueResultException
     */
    public function translationsDomain(Request $request, TranslationDomainRepository $domainRepository, TranslationUnitRepository $unitRepository)
    {
        $interface = $this->getInterface(TranslationUnit::class);
        $helper = $this->subscriber->get(IndexHelper::class);
        $helper->setDisplaySearchForm(true);

        $domain = $domainRepository->findDomain($request->get('translationdomain'));
        $helper->execute(TranslationUnit::class, $interface, 15, $unitRepository->findBy(['domain' => $domain]), true);
        $template = 'admin/page/translation/domain.html.twig';
        $arguments = [
            'domain' => $domain,
            'searchForm' => $helper->getSearchForm()->createView(),
            'pagination' => $helper->getPagination()
        ];

        if (!empty($request->get('ajax'))) {
            return new JsonResponse(['html' => $this->cache($template, $arguments, $request)]);
        }

        return $this->cache($template, $arguments);
    }

    /**
     * Delete TranslationDomain
     *
     * @Route("/delete/{translationdomain}", methods={"DELETE"}, name="admin_translationsdomain_delete")
     * @IsGranted("ROLE_INTERNAL")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        /** @var TranslationDomain $domain */
        $domain = $this->entityManager->getRepository(TranslationDomain::class)->find($request->get('translationdomain'));

        if (!$domain) {
            return new JsonResponse(['success' => false]);
        }

        $website = $this->getWebsite($request);
        $name = $domain->getName();

        foreach ($website->getConfiguration()->getAllLocales() as $locale) {

            $finder = new Finder();
            $dirname = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/translations/');
            $finder->files()->in($this->kernel->getProjectDir() . $dirname)->name($name . '+intl-icu.' . $locale . '.yaml');

            foreach ($finder as $file) {
                $filesystem = new Filesystem();
                $filesystem->remove($file->getPathname());
            }
        }

        return parent::delete($request);
    }
}