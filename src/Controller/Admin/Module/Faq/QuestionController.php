<?php

namespace App\Controller\Admin\Module\Faq;

use App\Controller\Admin\AdminController;
use App\Entity\Module\Faq\Question;
use App\Form\Type\Module\Faq\QuestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * QuestionController
 *
 * Faq Question Action management
 *
 * @Route("/admin-%security_token%/{website}/faqs/questions", schemes={"%protocol%"})
 * @IsGranted("ROLE_FAQ")
 *
 * @property Question $class
 * @property QuestionType $formType
 *
 * @author Sébastien FOURNIER <contact@sebastien-fournier.com>
 */
class QuestionController extends AdminController
{
    protected $class = Question::class;
    protected $formType = QuestionType::class;

    /**
     * Index Question
     *
     * @Route("/{faq}/index", methods={"GET", "POST"}, name="admin_faqquestion_index")
     *
     * {@inheritdoc}
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * New Question
     *
     * @Route("/{faq}/new", methods={"GET", "POST"}, name="admin_faqquestion_new")
     *
     * {@inheritdoc}
     */
    public function new(Request $request)
    {
        return parent::new($request);
    }

    /**
     * Edit Question
     *
     * @Route("/{faq}/edit/{faqquestion}", methods={"GET", "POST"}, name="admin_faqquestion_edit")
     *
     * {@inheritdoc}
     */
    public function edit(Request $request)
    {
        return parent::edit($request);
    }

    /**
     * Show Question
     *
     * @Route("/{faq}/show/{faqquestion}", methods={"GET"}, name="admin_faqquestion_show")
     *
     * {@inheritdoc}
     */
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * Position Question
     *
     * @Route("/{faq}/position/{faqquestion}", methods={"GET", "POST"}, name="admin_faqquestion_position")
     *
     * {@inheritdoc}
     */
    public function position(Request $request)
    {
        return parent::position($request);
    }

    /**
     * Delete Question
     *
     * @Route("/delete/{faqquestion}", methods={"DELETE"}, name="admin_faqquestion_delete")
     *
     * {@inheritdoc}
     */
    public function delete(Request $request)
    {
        return parent::delete($request);
    }
}