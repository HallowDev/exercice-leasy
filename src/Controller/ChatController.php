<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Messages;
use App\Form\ChatType;
use App\Form\MessageType;
use App\Repository\ChatRepository;
use App\Repository\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/chat")
 */
class ChatController extends AbstractController
{
    /**
     * @Route("/", name="app_chat_index", methods={"GET"})
     */
    public function index(ChatRepository $chatRepository): Response
    {
        return $this->render('chat/index.html.twig', [
            'chats' => $chatRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_chat_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ChatRepository $chatRepository): Response
    {
        $chat = new Chat();
        $form = $this->createForm(ChatType::class, $chat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chatRepository->add($chat, true);

            return $this->redirectToRoute('app_chat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chat/new.html.twig', [
            'chat' => $chat,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_chat_show", methods={"GET","POST"})
     */
    public function show(Chat $chat, MessagesRepository $messagesRepository, Request $request): Response
    {
        $message = new Messages();
        $message->setUser($this->getUser());
        $message->setChat($chat);

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $message->setCreatedAt(new \DateTimeImmutable());
            $messagesRepository->add($message, true);
            $this->redirectToRoute('app_chat_show', ["id" => $chat->getId()]);
        }


        return $this->render('chat/show.html.twig', [
            'chat' => $chat,
            'chats' => $messagesRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_chat_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Chat $chat, ChatRepository $chatRepository): Response
    {
        $form = $this->createForm(ChatType::class, $chat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $chatRepository->add($chat, true);

            return $this->redirectToRoute('app_chat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chat/edit.html.twig', [
            'chat' => $chat,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_chat_delete", methods={"POST"})
     */
    public function delete(Request $request, Chat $chat, ChatRepository $chatRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chat->getId(), $request->request->get('_token'))) {
            $chatRepository->remove($chat, true);
        }

        return $this->redirectToRoute('app_chat_index', [], Response::HTTP_SEE_OTHER);
    }
}
