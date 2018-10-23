<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;
use App\Form\AuthorFormType;
use App\Entity\BlogPost;
use App\Form\BlogPostFormType;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $authorRepository;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $blogPostRepository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
    }

    /**
     * @Route("/author-create", name="admin_author_create")
     */
    public function createAuthorAction(Request $request)
    {
        if ($this->authorRepository->findOneByUsername($this->getUser()->getUserName())) {
            // Redirect to dashboard.
            $this->addFlash('error', 'Unable to create author, author already exists!');

            return $this->redirectToRoute('admin_index');
        }

        $author = new Author();
        $author->setUsername($this->getUser()->getUserName());

        $form = $this->createForm(AuthorFormType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($author);
            $this->entityManager->flush($author);

            $request->getSession()->set('user_is_author', true);
            $this->addFlash('success', 'Congratulations! You are now an author. Write your first post!');

            return $this->redirectToRoute('admin_blog_post_create');
        }

        return $this->render('admin/create_author.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/create-blog-post", name="admin_blog_post_create")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createEntryAction(Request $request)
    {
        $blogPost = new BlogPost();

        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        $blogPost->setAuthor($author);

        $form = $this->createForm(BlogPostFormType::class, $blogPost);
        $form->handleRequest($request);

        // Check is valid
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($blogPost);
            
            $uploadableManager = $this->get('stof_doctrine_extensions.uploadable.manager');
            $uploadableManager->markEntityToUpload($blogPost, $blogPost->getFile());
            $this->entityManager->flush($blogPost);

            $this->addFlash('success', 'Congratulations! Your post is created');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/entry_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/", name="admin_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());

        $blogPosts = [];

        if ($author) {
            $blogPosts = $this->blogPostRepository->findByAuthor($author);
        }

        return $this->render('admin/index.html.twig', [
            'blogPosts' => $blogPosts
        ]);
    }

    /**
     * @Route("/delete-entry/{entryId}", name="admin_delete_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        $author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());

        if (!$blogPost || $author !== $blogPost->getAuthor()) {
            $this->addFlash('error', 'Unable to remove entry!');

            return $this->redirectToRoute('admin_index');
        }

        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();

        $this->addFlash('success', 'Entry was deleted!');

        return $this->redirectToRoute('admin_index');
    }
}
