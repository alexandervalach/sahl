<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\InvalidArgumentException;

class AlbumsPresenter extends BasePresenter {

    const ALBUM_NOT_FOUND = 'Album not found';

    /** @var ActiveRow */
    private $albumRow;

    /** @var ActiveRow */
    private $imgRow;

    /**
     * Passes prepared data to template
     */
    public function renderAll() {
        $this->template->albums = $this->albumsRepository->findAll();

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    /**
     * Loads album data
     *
     * @param ActiveRow|string $id
     */
    public function actionView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    /**
     * @param string $id
     */
    public function renderView(string $id) {
        if (!$this->albumRow) {
            throw new BadRequestException(self::ALBUM_NOT_FOUND);
        }

        $this->template->album = $this->albumRow;
        $this->template->imgs = $this->albumRow->related('images');

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->albumRow);
            $this->getComponent(self::REMOVE_FORM);
        }
    }

    /**
     * @param int $album_id
     * @param ActiveRow|string $id
     */
    public function actionSetImg(int $album_id, $id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($album_id);
        $this->imgRow = $this->imagesRepository->findById($id);
        $this->submittedSetImg();
    }

    /**
     * @param ActiveRow|string $id
     */
    public function actionRemoveImg($id) {
        $this->userIsLogged();
        $this->imgRow = $this->imagesRepository->findById($id);
        if (!$this->imgRow) {
            throw new BadRequestException(self::IMG_NOT_FOUND);
        }
        $this->submittedRemoveImg();
    }

    /**
     * Creates add album form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentAddForm()
    {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->setRequired("Názov je povinné pole.");
        $form->addSubmit('add', 'Pridať');
        $form->addSubmit('cancel', 'Zrušiť')
                ->setAttribute('class', self::BTN_WARNING)
                ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Creates edit album form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->setRequired("Názov je povinné pole");
        $form->addSubmit('edit', 'Upraviť')
                ->setAttribute('class', self::BTN_SUCCESS);
        $form->addSubmit('cancel', 'Zrušiť')
                ->setAttribute('class', self::BTN_WARNING)
                ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Creates remove album form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('remove', 'Odstrániť')
                ->setAttribute('class', self::BTN_DANGER);
        $form->addSubmit('cancel', 'Zrušiť')
                ->setAttribute('class', self::BTN_WARNING)
                ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Creates add image form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentAddImgForm() {
        $form = new Form;
        $form->addMultiUpload('files', 'Obrázky')
                ->addRule(Form::FILLED, 'Vyberte obrázky, prosím')
                ->addRule(Form::IMAGE, 'Obrázok môže byť len vo formáte JPEG, PNG alebo GIF.');
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_IMG_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Adds form values to database
     *
     * @param Nette\Application\UI\Form $form
     * @param array $values
     */
    public function submittedAddForm(Form $form, array $values) {
        $this->albumsRepository->insert($values);
        $this->flashMessage('Album bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Submites edited values to database
     *
     * @param Nette\Application\UI\Form $form
     * @param array $values
     */
    public function submittedEditForm(Form $form, array $values) {
        $this->albumRow->update($values);
        $this->flashMessage('Album bol upravený', self::SUCCESS);
        $this->redirect('view', $this->albumRow);
    }

    /***
     * Removes albums and related records from database
     */
    public function submittedRemoveForm() {
        $imgs = $this->albumRow->related('images');

        foreach ($imgs as $img) {
            try {
                FileSystem::delete($this->imageDir . $img->name);
            } catch (IOException $e) {
                $this->flashMessage('Nastala chyba, skúste znovu', self::DANGER);
                $this->redirect('all');
            }
            $img->delete();
        }

        $this->albumRow->delete();
        $this->flashMessage('Album bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Removes an image from database and filesystem
     */
    public function submittedRemoveImg() {
        $album = $this->imgRow->album_id;
        try {
            FileSystem::delete($this->imageDir . $this->imgRow->name);
            $this->imgRow->delete();
            $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
        } catch (IOException $e) {
            $this->flashMessage('Obrázok sa nepodarilo odstrániť', self::DANGER);
        }
        $this->redirect('Albums:view', $album);
    }

    public function submittedSetImg() {
        $data['thumbnail'] = $this->imgRow->name;
        $this->albumRow->update($data);
        $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Adds image into database and filesystem
     *
     * @param Nette\Application\UI\Form $form
     * @param array $values
     */
    public function submittedAddImgForm(Form $form, $values) {
        $data = array();

        foreach ($values['files'] as $img) {
            $name = strtolower($img->getSanitizedName());
            $data = array('name' => $name, 'album_id' => $this->albumRow);

            if (!$img->isOk() OR !$img->isImage()) {
                throw new InvalidArgumentException;
            }

            if (!$img->move($this->imageDir . '/' . $name)) {
                throw new IOException;
            }

            $this->imagesRepository->insert($data);
        }

        $this->flashMessage('Obrázky boli pridané', self::SUCCESS);
        $this->redirect('Albums:view', $this->albumRow);
    }

}
