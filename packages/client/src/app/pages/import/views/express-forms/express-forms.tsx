import React from 'react';

import { useImportPreviewQuery } from '../../import.queries';
import { CommonImportView } from '../common/common';

export const ImportExpressForms: React.FC = () => {
  const { data, isFetching } = useImportPreviewQuery(
    '/import/express-forms/preview'
  );

  return <CommonImportView data={data} isFetching={isFetching} />;
};
