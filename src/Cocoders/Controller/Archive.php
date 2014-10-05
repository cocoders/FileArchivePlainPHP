<?php

namespace Cocoders\Controller;

use Cocoders\Archive\InMemoryArchive\InMemoryArchiveFactory;
use Cocoders\Connection;
use Cocoders\FileSource\DummyFileSource\DummyFileSource;
use Cocoders\FileSource\InMemoryFileSource\InMemoryFileSourceRegistry;
use Cocoders\FormType\CreateArchiveFormType;
use Cocoders\Repository\PgsqlArchiveRepository;
use Cocoders\UseCase\CreateArchive\CreateArchiveRequest;
use Cocoders\UseCase\CreateArchive\CreateArchiveResponder;
use Cocoders\UseCase\CreateArchive\CreateArchiveUseCase;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;

class Archive implements CreateArchiveResponder
{
    private $paths = [
        '/home/leszek/aaa.jpg', '/home/leszek/test.txt', '/home/leszek/test.abc/test.txt',
        '/home/leszek/test.abc/bbb.jpg', '/home/leszek/test.abc/cc.jpg'
    ];
    private $createArchiveUseCase;
    private $twig;
    private $response;

    public function __construct(\Twig_Environment $twig, Connection $connection)
    {
        $this->twig = $twig;
        $fileSourceRegistry = new InMemoryFileSourceRegistry();
        $fileSourceRegistry->registerFileSource('dummy', new DummyFileSource($this->paths));
        $archiveRepository = new PgsqlArchiveRepository($connection);
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory()
        ;
        $this->createArchiveUseCase = new CreateArchiveUseCase(
            $fileSourceRegistry,
            new InMemoryArchiveFactory(),
            $archiveRepository
        );
        $this->listArchiveUseCase = new ArchiveListUseCase($archiveRepository);
        $this->createArchiveUseCase->addResponder($this);
    }

    public function create(Request $request)
    {
        $form = $this->formFactory->create(new CreateArchiveFormType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $createArchiveData = $form->getData();

            $this->createArchiveUseCase->execute(
                new CreateArchiveRequest('dummy', $createArchiveData['archiveName'], $createArchiveData['path'])
            );

            return $this->response;
        }

        return new Response(
            $this->twig->render('createArchive.html.twig', [
                'form' => $form->createView()
            ])
        );
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function archiveCreated($name)
    {
        $this->response = new Response('Archive created');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function archiveAlreadyExists($name)
    {
        $this->response = new Response('Archive already exists');
    }
}