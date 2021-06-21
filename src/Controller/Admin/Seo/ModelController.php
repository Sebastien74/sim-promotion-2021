<?php

namespace App\Controller\Admin\Seo;

use App\Entity\Seo\Model;
use App\Form\Type\Seo\ModelType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ModelController
 *
 * SEO Model management
 *
 * @Route("/admin-%security_token%/{website}/seo/models", schemes={"%protocol%"})
 * @IsGranted("ROLE_SEO")
 *
 * @property Model $entity
 * @property Model $class
 * @property ModelType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class ModelController extends BaseController
{
    /** @var Model entity */
    protected $entity;
    protected $class = Model::class;
    protected $formType = ModelType::class;

    /**
     * Edit Seo Model
     *
     * @Route("/edit/{model}/{entitylocale}", methods={"GET", "POST"}, name="admin_seo_model_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        $this->entity = $this->entityManager->getRepository(Model::class)->find($request->get('model'));
        if (!$this->entity) {
            throw $this->createNotFoundException($this->translator->trans("Ce modèle n'existe pas !!", [], 'admin'));
        }

        $website = $this->getWebsite($request);
        $this->getKeyWords();
        $this->template = 'admin/page/seo/edit-model.html.twig';
        $this->getEntities($request, $website);
        $this->arguments['currentCategory'] = 'models';
        $this->arguments['currentUrl'] = NULL;

        return parent::edit($request);
    }

    /**
     * Get keywords
     */
    private function getKeyWords()
    {
        $metadata = $this->entityManager->getClassMetadata($this->entity->getClassName());
        $classname = $metadata->getName();
        $entity = new $classname();
        $interface = $this->getInterface($classname);
        $this->arguments['modelInterface'] = $interface;

        if (!empty($interface['seo'])) {
            $this->arguments['labels'] = method_exists($entity, "getLabels") && !empty($entity::getLabels())
                ? $entity::getLabels() : NULL;
            $this->arguments['keywords']['models'] = $interface['seo'];
            $this->arguments['haveCaption'] = true;
        } else {

            $explodeEntity = explode("\\", $classname);
            $keywords[end($explodeEntity)] = $metadata->getFieldNames();

            $fieldsMapping = $metadata->getAssociationNames();
            foreach ($fieldsMapping as $key => $field) {
                $subEntity = $metadata->getAssociationTargetClass($field);
                $subEntityFields = $this->entityManager->getClassMetadata($subEntity)->getFieldNames();
                foreach ($subEntityFields as $subEntityKey => $subEntityField) {
                    $subEntityFields[$subEntityKey] = $field . '.' . $subEntityField;
                }
                $keywords[$field] = $subEntityFields;
            }

            $this->arguments['keywords'] = $keywords;
            $this->arguments['haveCaption'] = false;
        }
    }
}