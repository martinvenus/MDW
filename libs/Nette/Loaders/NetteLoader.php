<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Loaders
 */



/**
 * Nette auto loader is responsible for loading Nette classes and interfaces.
 *
 * @author     David Grudl
 */
class NNetteLoader extends NAutoLoader
{
	/** @var NNetteLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'argumentoutofrangeexception' => '/Utils/exceptions.php',
		'datetime53' => '/Utils/DateTime53.php',
		'deprecatedexception' => '/Utils/exceptions.php',
		'directorynotfoundexception' => '/Utils/exceptions.php',
		'fatalerrorexception' => '/Utils/exceptions.php',
		'filenotfoundexception' => '/Utils/exceptions.php',
		'iannotation' => '/Reflection/IAnnotation.php',
		'iauthenticator' => '/Security/IAuthenticator.php',
		'iauthorizator' => '/Security/IAuthorizator.php',
		'icachestorage' => '/Caching/ICacheStorage.php',
		'icollection' => '/Collections/ICollection.php',
		'icomponent' => '/ComponentModel/IComponent.php',
		'icomponentcontainer' => '/ComponentModel/IComponentContainer.php',
		'iconfigadapter' => '/Config/IConfigAdapter.php',
		'idebuggable' => '/Debug/IDebuggable.php',
		'ifiletemplate' => '/Templates/IFileTemplate.php',
		'iformcontrol' => '/Forms/IFormControl.php',
		'iformrenderer' => '/Forms/IFormRenderer.php',
		'ihttprequest' => '/Web/IHttpRequest.php',
		'ihttpresponse' => '/Web/IHttpResponse.php',
		'iidentity' => '/Security/IIdentity.php',
		'ilist' => '/Collections/IList.php',
		'imailer' => '/Mail/IMailer.php',
		'imap' => '/Collections/IMap.php',
		'inamingcontainer' => '/Forms/INamingContainer.php',
		'invalidstateexception' => '/Utils/exceptions.php',
		'ioexception' => '/Utils/exceptions.php',
		'ipartiallyrenderable' => '/Application/IRenderable.php',
		'ipermissionassertion' => '/Security/IPermissionAssertion.php',
		'ipresenter' => '/Application/IPresenter.php',
		'ipresenterloader' => '/Application/IPresenterLoader.php',
		'ipresenterresponse' => '/Application/IPresenterResponse.php',
		'irenderable' => '/Application/IRenderable.php',
		'iresource' => '/Security/IResource.php',
		'irole' => '/Security/IRole.php',
		'irouter' => '/Application/IRouter.php',
		'iservicelocator' => '/Environment/IServiceLocator.php',
		'iset' => '/Collections/ISet.php',
		'isignalreceiver' => '/Application/ISignalReceiver.php',
		'istatepersistent' => '/Application/IStatePersistent.php',
		'isubmittercontrol' => '/Forms/ISubmitterControl.php',
		'itemplate' => '/Templates/ITemplate.php',
		'itranslator' => '/Utils/ITranslator.php',
		'iuser' => '/Web/IUser.php',
		'memberaccessexception' => '/Utils/exceptions.php',
		'nabortexception' => '/Application/Exceptions/AbortException.php',
		'nambiguousserviceexception' => '/Environment/ServiceLocator.php',
		'nannotation' => '/Reflection/Annotation.php',
		'nannotations' => '/Reflection/Annotations.php',
		'nannotationsparser' => '/Reflection/AnnotationsParser.php',
		'nappform' => '/Application/AppForm.php',
		'napplication' => '/Application/Application.php',
		'napplicationexception' => '/Application/Exceptions/ApplicationException.php',
		'narraylist' => '/Collections/ArrayList.php',
		'narraytools' => '/Utils/ArrayTools.php',
		'nauthenticationexception' => '/Security/AuthenticationException.php',
		'nautoloader' => '/Loaders/AutoLoader.php',
		'nbadrequestexception' => '/Application/Exceptions/BadRequestException.php',
		'nbadsignalexception' => '/Application/Exceptions/BadSignalException.php',
		'nbasetemplate' => '/Templates/BaseTemplate.php',
		'nbutton' => '/Forms/Controls/Button.php',
		'ncache' => '/Caching/Cache.php',
		'ncachinghelper' => '/Templates/Filters/CachingHelper.php',
		'ncallback' => '/Utils/Callback.php',
		'ncheckbox' => '/Forms/Controls/Checkbox.php',
		'nclassreflection' => '/Reflection/ClassReflection.php',
		'nclirouter' => '/Application/Routers/CliRouter.php',
		'nclosurefix' => '/Utils/Framework.php',
		'ncollection' => '/Collections/Collection.php',
		'ncomponent' => '/ComponentModel/Component.php',
		'ncomponentcontainer' => '/ComponentModel/ComponentContainer.php',
		'nconfig' => '/Config/Config.php',
		'nconfigadapterini' => '/Config/ConfigAdapterIni.php',
		'nconfigurator' => '/Environment/Configurator.php',
		'ncontrol' => '/Application/Control.php',
		'nconventionalrenderer' => '/Forms/Renderers/ConventionalRenderer.php',
		'ncurlybracketsfilter' => '/Templates/Filters/LatteFilter.php',
		'ncurlybracketsmacros' => '/Templates/Filters/LatteFilter.php',
		'ndebug' => '/Debug/Debug.php',
		'ndownloadresponse' => '/Application/Responses/DownloadResponse.php',
		'ndummystorage' => '/Caching/DummyStorage.php',
		'nenvironment' => '/Environment/Environment.php',
		'nextensionreflection' => '/Reflection/ExtensionReflection.php',
		'nfilestorage' => '/Caching/FileStorage.php',
		'nfileupload' => '/Forms/Controls/FileUpload.php',
		'nforbiddenrequestexception' => '/Application/Exceptions/ForbiddenRequestException.php',
		'nform' => '/Forms/Form.php',
		'nformcontainer' => '/Forms/FormContainer.php',
		'nformcontrol' => '/Forms/Controls/FormControl.php',
		'nformgroup' => '/Forms/FormGroup.php',
		'nforwardingresponse' => '/Application/Responses/ForwardingResponse.php',
		'nframework' => '/Utils/Framework.php',
		'nfreezableobject' => '/Utils/FreezableObject.php',
		'nftp' => '/Web/Ftp.php',
		'nftpexception' => '/Web/Ftp.php',
		'nfunctionreflection' => '/Reflection/FunctionReflection.php',
		'ngenericrecursiveiterator' => '/Utils/Iterators/GenericRecursiveIterator.php',
		'nhashtable' => '/Collections/Hashtable.php',
		'nhiddenfield' => '/Forms/Controls/HiddenField.php',
		'nhtml' => '/Web/Html.php',
		'nhttpcontext' => '/Web/HttpContext.php',
		'nhttprequest' => '/Web/HttpRequest.php',
		'nhttpresponse' => '/Web/HttpResponse.php',
		'nhttpuploadedfile' => '/Web/HttpUploadedFile.php',
		'nidentity' => '/Security/Identity.php',
		'nimage' => '/Utils/Image.php',
		'nimagebutton' => '/Forms/Controls/ImageButton.php',
		'nimagemagick' => '/Utils/ImageMagick.php',
		'ninstancefilteriterator' => '/Utils/Iterators/InstanceFilterIterator.php',
		'ninstantclientscript' => '/Forms/Renderers/InstantClientScript.php',
		'ninvalidlinkexception' => '/Application/Exceptions/InvalidLinkException.php',
		'ninvalidpresenterexception' => '/Application/Exceptions/InvalidPresenterException.php',
		'njsonresponse' => '/Application/Responses/JsonResponse.php',
		'nkeynotfoundexception' => '/Collections/Hashtable.php',
		'nlattefilter' => '/Templates/Filters/LatteFilter.php',
		'nlattemacros' => '/Templates/Filters/LatteMacros.php',
		'nlimitedscope' => '/Loaders/LimitedScope.php',
		'nlink' => '/Application/Link.php',
		'nmail' => '/Mail/Mail.php',
		'nmailmimepart' => '/Mail/MailMimePart.php',
		'nmemcachedstorage' => '/Caching/MemcachedStorage.php',
		'nmethodreflection' => '/Reflection/MethodReflection.php',
		'nmultirouter' => '/Application/Routers/MultiRouter.php',
		'nmultiselectbox' => '/Forms/Controls/MultiSelectBox.php',
		'nnetteloader' => '/Loaders/NetteLoader.php',
		'nobject' => '/Utils/Object.php',
		'nobjectmixin' => '/Utils/ObjectMixin.php',
		'notimplementedexception' => '/Utils/exceptions.php',
		'notsupportedexception' => '/Utils/exceptions.php',
		'npaginator' => '/Utils/Paginator.php',
		'nparameterreflection' => '/Reflection/ParameterReflection.php',
		'npermission' => '/Security/Permission.php',
		'npresenter' => '/Application/Presenter.php',
		'npresentercomponent' => '/Application/PresenterComponent.php',
		'npresentercomponentreflection' => '/Application/PresenterComponentReflection.php',
		'npresenterloader' => '/Application/PresenterLoader.php',
		'npresenterrequest' => '/Application/PresenterRequest.php',
		'npropertyreflection' => '/Reflection/PropertyReflection.php',
		'nradiolist' => '/Forms/Controls/RadioList.php',
		'nrecursivecomponentiterator' => '/ComponentModel/ComponentContainer.php',
		'nredirectingresponse' => '/Application/Responses/RedirectingResponse.php',
		'nrenderresponse' => '/Application/Responses/RenderResponse.php',
		'nrobotloader' => '/Loaders/RobotLoader.php',
		'nroute' => '/Application/Routers/Route.php',
		'nrule' => '/Forms/Rule.php',
		'nrules' => '/Forms/Rules.php',
		'nselectbox' => '/Forms/Controls/SelectBox.php',
		'nsendmailmailer' => '/Mail/SendmailMailer.php',
		'nservicelocator' => '/Environment/ServiceLocator.php',
		'nsession' => '/Web/Session.php',
		'nsessionnamespace' => '/Web/SessionNamespace.php',
		'nset' => '/Collections/Set.php',
		'nsimpleauthenticator' => '/Security/SimpleAuthenticator.php',
		'nsimplerouter' => '/Application/Routers/SimpleRouter.php',
		'nsmartcachingiterator' => '/Utils/Iterators/SmartCachingIterator.php',
		'nsnippethelper' => '/Templates/Filters/SnippetHelper.php',
		'nstring' => '/Utils/String.php',
		'nsubmitbutton' => '/Forms/Controls/SubmitButton.php',
		'ntemplate' => '/Templates/Template.php',
		'ntemplatecachestorage' => '/Templates/TemplateCacheStorage.php',
		'ntemplatefilters' => '/Templates/Filters/TemplateFilters.php',
		'ntemplatehelpers' => '/Templates/Filters/TemplateHelpers.php',
		'ntextarea' => '/Forms/Controls/TextArea.php',
		'ntextbase' => '/Forms/Controls/TextBase.php',
		'ntextinput' => '/Forms/Controls/TextInput.php',
		'ntools' => '/Utils/Tools.php',
		'nuri' => '/Web/Uri.php',
		'nuriscript' => '/Web/UriScript.php',
		'nuser' => '/Web/User.php',
		'safestream' => '/Utils/SafeStream.php',
	);



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return NNetteLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			NLimitedScope::load(NETTE_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}
