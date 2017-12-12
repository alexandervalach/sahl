<?php

namespace App\Presenters;

use App\BreadCrumb\BreadCrumb;
use App\FormHelper;
use App\Model\AlbumsRepository;
use App\Model\ArchiveRepository;
use App\Model\EventsRepository;
use App\Model\FightsRepository;
use App\Model\ForumRepository;
use App\Model\GalleryRepository;
use App\Model\GoalsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use App\Model\PostImageRepository;
use App\Model\PostsRepository;
use App\Model\PunishmentsRepository;
use App\Model\ReplyRepository;
use App\Model\RulesRepository;
use App\Model\RoundsRepository;
use App\Model\TableTypesRepository;
use App\Model\TablesRepository;
use App\Model\TeamsRepository;
use App\Model\UsersRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    /** @var AlbumsRepository */
    protected $albumsRepository;

    /** @var ArchiveRepository */
    protected $archiveRepository;

    /** @var EventsRepository */
    protected $eventsRepository;

    /** @var FightsRepository */
    protected $fightsRepository;

    /** @var ForumRepository */
    protected $forumRepository;

    /** @var GalleryRepository */
    protected $galleryRepository;

    /** @var GoalsRepository */
    protected $goalsRepository;

    /** @var LinksRepository */
    protected $linksRepository;
    
    /** @var TableTypesRepository */
    protected $tableTypesRepository;
    
    /** @var PlayerTypesRepository */
    protected $playerTypesRepository;

    /** @var PlayersRepository */
    protected $playersRepository;

    /** @var PostImageRepository */
    protected $postImageRepository;

    /** @var PostsRepository */
    protected $postsRepository;

    /** @var PunishmentsRepository */
    protected $punishmentsRepository;

    /** @var ReplyRepository */
    protected $replyRepository;

    /** @var RoundsRepository */
    protected $roundsRepository;

    /** @var RulesRepository */
    protected $rulesRepository;

    /** @var TablesRepository */
    protected $tablesRepository;

    /** @var TeamsRepository */
    protected $teamsRepository;

    /** @var UsersRepository */
    protected $usersRepository;

    /** @var string */
    protected $imgFolder;

    /** @var string */
    protected $default_img;

    public function __construct(
    ArchiveRepository $archiveRepository, AlbumsRepository $albumsRepository, EventsRepository $eventsRepository, FightsRepository $fightsRepository, ForumRepository $forumRepository, GalleryRepository $galleryRepository, GoalsRepository $goalsRepository, LinksRepository $linksRepository, tableTypesRepository $tableTypesRepository, PlayerTypesRepository $playerTypesRepository, PlayersRepository $playersRepository, PostImageRepository $postImageRepository, PostsRepository $postsRepository, PunishmentsRepository $punishmentsRepository, ReplyRepository $replyRepository, RoundsRepository $roundsRepository, RulesRepository $rulesRepository, TablesRepository $tablesRepository, TeamsRepository $teamsRepository, UsersRepository $usersRepository) {
        parent::__construct();
        $this->archiveRepository = $archiveRepository;
        $this->albumsRepository = $albumsRepository;
        $this->eventsRepository = $eventsRepository;
        $this->fightsRepository = $fightsRepository;
        $this->forumRepository = $forumRepository;
        $this->galleryRepository = $galleryRepository;
        $this->goalsRepository = $goalsRepository;
        $this->linksRepository = $linksRepository;
        $this->tableTypesRepository = $tableTypesRepository;
        $this->playersRepository = $playersRepository;
        $this->playerTypesRepository = $playerTypesRepository;
        $this->postImageRepository = $postImageRepository;
        $this->postsRepository = $postsRepository;
        $this->punishmentsRepository = $punishmentsRepository;
        $this->replyRepository = $replyRepository;
        $this->roundsRepository = $roundsRepository;
        $this->rulesRepository = $rulesRepository;
        $this->tablesRepository = $tablesRepository;
        $this->teamsRepository = $teamsRepository;
        $this->usersRepository = $usersRepository;
        $this->default_img = "sahl.png";
        $this->imgFolder = "images";
    }

    protected function createComponentDeleteForm() 
    {
        $form = new Form;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->onClick[] = [$this, 'formCancelled'];
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger')
             ->onClick[] = [$this, 'submittedDeleteForm'];
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentSignInForm() 
    {
        $form = new Form;
        $form->addText('username', 'Používateľské meno')
             ->setRequired('Zadajte používateľské meno.');
        $form->addPassword('password', 'Heslo')
             ->setRequired('Zadajte heslo.');
        $form->addSubmit('login', 'Administrácia')
             ->setAttribute('class', 'btn btn-success');
        $form->addProtection();
        $form->onSuccess[] = [$this, 'submittedSignInForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSignInForm(Form $form, $values) 
    {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');
        } catch (AuthenticationException $e) {
            $this->flashMessage('Nesprávne meno alebo heslo.', 'error');
            $this->redirect('Homepage:');
        }
    }

    public function actionOut() 
    {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený.', 'success');
        $this->redirect('Homepage:');
    }

    protected function userIsLogged() 
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Homepage:');
        }
    }

    protected function createComponentBreadCrumb() 
    {
        $breadCrumb = new BreadCrumb();
        $breadCrumb->addLink('Domov', $this->link('Homepage:'));
        return $breadCrumb;
    }

}
