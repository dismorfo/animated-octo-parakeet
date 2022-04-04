import React, { useEffect } from 'react';
import PropTypes from 'prop-types';
import { render } from 'react-dom';
import Mirador from 'mirador/dist/es/src/index';
import { miradorImageToolsPlugin } from 'mirador-image-tools';

function MiradorViewer(props) {
  const { manifest, sequence } = props;
  const canvasIndexValue = Number(sequence) - 1;
  const uuid = 'viewer-demo';
  const config = {
    id: uuid,
    workspaceControlPanel: {
      enabled: false,
    },
    workspace: {
      isWorkspaceAddVisible: false,
      allowNewWindows: false,
    },
    language: 'en',
    windows: [
      {
        manifestId: manifest,
        imageToolsEnabled: true,
        imageToolsOpen: false,
        canvasIndex: canvasIndexValue,
        view: 'single',
      },
    ],
    window: {
      allowClose: false,
      defaultSideBarPanel: 'info',
      sideBarOpenByDefault: false,
      showLocalePicker: true,
      hideWindowTitle: true,
    },
  };
  useEffect(() => {
    Mirador.viewer(config, [
      ...miradorImageToolsPlugin,
    ]);
  });
  return (
    <div id={config.id} />
  );
}

MiradorViewer.propTypes = {
  sequence: PropTypes.string.isRequired,
  manifest: PropTypes.string.isRequired,
};

const elm = document.getElementById('app');

render(<MiradorViewer sequence={elm.dataset.sequence} manifest={elm.dataset.manifest} />, elm);
