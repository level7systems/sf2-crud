<?php

namespace Demo\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Demo\UserBundle\Entity\PhoneNumber;
use Demo\UserBundle\Form\PhoneNumberType;
use Demo\UserBundle\Form\PhoneNumberFilterType;

/**
 * PhoneNumber controller.
 *
 * @Route("/phonenumbers")
 */
class PhoneNumberController extends Controller
{
    /**
     * Lists all PhoneNumber entities.
     *
     * @Route("/", name="phonenumbers")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        );
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new PhoneNumberFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('DemoUserBundle:PhoneNumber')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('PhoneNumberControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('PhoneNumberControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('PhoneNumberControllerFilter')) {
                $filterData = $session->get('PhoneNumberControllerFilter');
                $filterForm = $this->createForm(new PhoneNumberFilterType(), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me)
        {
            return $me->generateUrl('phonenumbers', array('page' => $page));
        };

        // Paginator - view
        $translator = $this->get('translator');
        $view = new TwitterBootstrapView();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => $translator->trans('views.index.pagprev', array(), 'JordiLlonchCrudGeneratorBundle'),
            'next_message' => $translator->trans('views.index.pagnext', array(), 'JordiLlonchCrudGeneratorBundle'),
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Creates a new PhoneNumber entity.
     *
     * @Route("/", name="phonenumbers_create")
     * @Method("POST")
     * @Template("DemoUserBundle:PhoneNumber:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new PhoneNumber();
        $form = $this->createForm(new PhoneNumberType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('phonenumbers_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new PhoneNumber entity.
     *
     * @Route("/new", name="phonenumbers_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new PhoneNumber();
        $form   = $this->createForm(new PhoneNumberType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a PhoneNumber entity.
     *
     * @Route("/{id}", name="phonenumbers_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DemoUserBundle:PhoneNumber')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhoneNumber entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing PhoneNumber entity.
     *
     * @Route("/{id}/edit", name="phonenumbers_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DemoUserBundle:PhoneNumber')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhoneNumber entity.');
        }

        $editForm = $this->createForm(new PhoneNumberType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing PhoneNumber entity.
     *
     * @Route("/{id}", name="phonenumbers_update")
     * @Method("PUT")
     * @Template("DemoUserBundle:PhoneNumber:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DemoUserBundle:PhoneNumber')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PhoneNumber entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new PhoneNumberType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('phonenumbers_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a PhoneNumber entity.
     *
     * @Route("/{id}", name="phonenumbers_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DemoUserBundle:PhoneNumber')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PhoneNumber entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('phonenumbers'));
    }

    /**
     * Creates a form to delete a PhoneNumber entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
