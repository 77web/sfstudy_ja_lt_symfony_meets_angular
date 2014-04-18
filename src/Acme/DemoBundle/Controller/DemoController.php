<?php

namespace Acme\DemoBundle\Controller;

use Acme\DemoBundle\Data\DummyUser;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Acme\DemoBundle\Form\ContactType;

// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DemoController extends Controller
{
    /**
     * @Route("/", name="_demo")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/hello/{name}", name="_demo_hello")
     * @Template()
     */
    public function helloAction($name)
    {
        return array('name' => $name);
    }

    /**
     * @Route("/contact", name="_demo_contact")
     * @Template()
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(new ContactType());

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('session')->getFlashBag()->set('notice', 'メッセージ送信完了！');

            return new RedirectResponse($this->generateUrl('_demo_contact'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @param Request $request
     * @Route("/contact.json", name="_demo_contact_json")
     * @Method("POST")
     * @return Response
     */
    public function contactSendAction(Request $request)
    {
        $form = $this->createForm(new ContactType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            // ここに送信処理

            $data = '';
            $status = 200;
        } else {
            $data = [];
            foreach ($form as $name => $childForm) {
                /** @var \Symfony\Component\Form\FormInterface $childForm */
                if (!$childForm->isValid()) {
                    $data[$name] = [];
                    foreach ($childForm->getErrors() as $formError) {
                        /** @var \Symfony\Component\Form\FormError $formError */
                        $data[$name][] = $formError->getMessage();
                    }
                }
            }

            $status = 500;
        }
        $response = new Response($this->get('jms_serializer')->serialize($data, 'json'), $status);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/users", name="_demo_rest_frontend")
     * @Template()
     */
    public function usersAction()
    {
        return [];
    }

    /**
     * @Route("/rest/users.json", name="_demo_rest_users")
     * @return Response
     */
    public function restUsersAction()
    {
        $user1 = new DummyUser(1, 'user1', 'I live in Nagoya');
        $user2 = new DummyUser(2, 'user2', 'I am from Sapporo');
        $user3 = new DummyUser(3, 'user3', 'I have a daughter and a son');
        $user4 = new DummyUser(4, 'user4', 'I like to play tennis');
        $user5 = new DummyUser(5, 'user5', 'I love chocolates');

        $users = [$user1, $user2, $user3, $user4, $user5];

        $response = new Response($this->get('jms_serializer')->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(['user.list'])
        ));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
